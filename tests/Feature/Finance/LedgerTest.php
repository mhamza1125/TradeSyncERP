<?php

namespace Tests\Feature\Finance;

use App\Services\Finance\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsFinance;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-09 / F-21 / FN-1: one balance rule, correct opening balances, exact decimals. */
class LedgerTest extends TestCase
{
    use BuildsFinance, BuildsInspections, RefreshDatabase;

    private LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->ledger = app(LedgerService::class);
    }

    public function test_effect_rule_per_transaction_type(): void
    {
        $cash = $this->makeAccount('Cash', '1000.00');
        $bank = $this->makeAccount('Bank', '0.00', 'Bank');
        $this->postTxn('CustomerReceipt', $cash, '300.00', '2026-01-02');
        $this->postTxn('Expense', $cash, '100.00', '2026-01-03');
        $this->postTxn('Salary', $cash, '50.00', '2026-01-04');
        $this->postTxn('JournalEntry', $cash, '200.00', '2026-01-05', to: $bank); // cash → bank

        $this->assertSame('950.00', $this->ledger->balanceAt($cash));
        $this->assertSame('200.00', $this->ledger->balanceAt($bank));
    }

    public function test_opening_balance_includes_everything_before_the_from_date(): void
    {
        // The audit's worked example.
        $cash = $this->makeAccount('Cash', '100000.00');
        $this->postTxn('Expense', $cash, '30000.00', '2026-01-15');

        $statement = $this->ledger->statement($cash, '2026-02-01', null);

        $this->assertSame('70000.00', $statement->opening);
        $this->assertSame('70000.00', $statement->closing);

        $this->get(route('ledger.cash', ['account_id' => $cash->id, 'from_date' => '2026-02-01']))
            ->assertOk()
            ->assertSee('70,000.00');
    }

    public function test_each_page_opens_where_the_previous_page_closed(): void
    {
        $cash = $this->makeAccount('Cash', '0.00');
        for ($i = 1; $i <= 120; $i++) {
            $this->postTxn('CustomerReceipt', $cash, '1.10', '2026-03-01'); // same date: order by id
        }

        $page1 = $this->ledger->statement($cash, null, null, perPage: 50, page: 1);
        $page2 = $this->ledger->statement($cash, null, null, perPage: 50, page: 2);
        $page3 = $this->ledger->statement($cash, null, null, perPage: 50, page: 3);

        $this->assertSame('55.00', $page1->closing);
        $this->assertSame($page1->closing, $page2->opening);
        $this->assertSame($page2->closing, $page3->opening);
        $this->assertSame('132.00', $page3->closing);
        $this->assertSame('132.00', $this->ledger->balanceAt($cash));
    }

    public function test_decimal_sums_are_exact(): void
    {
        $cash = $this->makeAccount('Cash', '0.00');
        $this->postTxn('CustomerReceipt', $cash, '0.10', '2026-01-01');
        $this->postTxn('CustomerReceipt', $cash, '0.20', '2026-01-02');

        $statement = $this->ledger->statement($cash, null, null);

        $this->assertSame('0.30', $statement->closing);
        $this->assertSame(['0.10', '0.30'], $statement->rows->pluck('running_balance')->all());
    }

    public function test_pdf_closing_matches_the_page(): void
    {
        $cash = $this->makeAccount('Cash', '500.00');
        $this->postTxn('CustomerReceipt', $cash, '1234.56', '2026-01-01');
        $this->postTxn('Expense', $cash, '34.56', '2026-01-02');

        $response = $this->get(route('ledger.cash.export-pdf', ['account_id' => $cash->id]))->assertOk();
        $text = $this->pdfText($response->getContent());

        $this->assertStringContainsString('1,700.00', $text);
        $this->assertSame('1700.00', $this->ledger->statement($cash, null, null)->closing);
    }

    public function test_transfer_check_uses_the_ledger_balance(): void
    {
        $cash = $this->makeAccount('Cash', '1000.00');
        $bank = $this->makeAccount('Bank', '0.00', 'Bank');
        $this->postTxn('Expense', $cash, '800.00', '2026-01-01'); // ledger balance now 200

        $this->post(route('transfers.store'), [
            'from_account_id' => $cash->id, 'to_account_id' => $bank->id,
            'amount' => '500.00', 'transaction_date' => '2026-01-02',
        ])->assertSessionHasErrors('amount');

        $this->post(route('transfers.store'), [
            'from_account_id' => $cash->id, 'to_account_id' => $bank->id,
            'amount' => '200.00', 'transaction_date' => '2026-01-02',
        ])->assertSessionHasNoErrors();

        $this->assertSame('0.00', $this->ledger->balanceAt($cash));
        $this->assertSame('200.00', $this->ledger->balanceAt($bank));
    }

    private function pdfText(string $pdf): string
    {
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($file, $pdf);
        $text = (string) shell_exec('pdftotext -layout '.escapeshellarg($file).' - 2>&1');
        @unlink($file);

        if ($text === '' || str_contains($text, 'not recognized') || str_contains($text, 'not found')) {
            $this->markTestSkipped('pdftotext is not available.');
        }

        return $text;
    }
}

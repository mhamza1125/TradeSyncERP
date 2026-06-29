<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($request->search, function ($q, $s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('subject_type', 'like', "%{$s}%")
                  ->orWhere('properties', 'like', "%{$s}%");
            })
            ->when($request->causer, fn ($q, $c) => $q->where('causer_id', $c))
            ->when($request->event, fn ($q, $e) => $q->where('description', $e))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('activities.index', compact('activities'));
    }

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);

        $old        = $activity->properties->get('old', []);
        $attributes = $activity->properties->get('attributes', []);

        return view('activities.show', compact('activity', 'old', 'attributes'));
    }

    // ── Static helpers (used in Blade via @php or passed as closures) ──────────

    public static function modelLabel(string $class): string
    {
        $base = class_basename($class);
        return match ($base) {
            'CustomerOrder'   => 'Customer Order',
            'CustomerInvoice' => 'Customer Invoice',
            'CustomerPayment' => 'Customer Payment',
            'SalaryRun'       => 'Salary Run',
            'ExpenseHead'     => 'Expense Head',
            'ProductCategory' => 'Product Category',
            'InspectionRun'   => 'Inspection Run',
            'InspectionType'  => 'Inspection Type',
            'SampleMovement'  => 'Sample Movement',
            'MovementItem'    => 'Movement Item',
            default           => trim(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $base)),
        };
    }

    public static function subjectIdentifier(Activity $activity): string
    {
        $subject = $activity->subject;
        $base    = class_basename($activity->subject_type ?? '');

        if (!$subject) {
            return '#' . $activity->subject_id;
        }

        return match ($base) {
            'CustomerOrder'   => $subject->order_code ?? "#{$subject->id}",
            'Sample'          => $subject->sample_code ?? "#{$subject->id}",
            'Customer'        => "\"{$subject->customer_name}\"",
            'CustomerInvoice' => $subject->invoice_number ?? "#{$subject->id}",
            'CustomerPayment' => $subject->invoice_reference
                                    ? "Ref: {$subject->invoice_reference}"
                                    : "Payment #{$subject->id}",
            'Expense'         => "#{$subject->id}",
            'Movement'        => "#{$subject->id}",
            'Employee'        => $subject->employee_name ?? "#{$subject->id}",
            'Supplier'        => $subject->name ?? "#{$subject->id}",
            'Inspection'      => $subject->report_number ?? "#{$subject->id}",
            'SalaryRun'       => "Run #{$subject->id}",
            'Currency'        => $subject->currency_code ?? "#{$subject->id}",
            'Bank'            => $subject->bank_name ?? "#{$subject->id}",
            'Account'         => $subject->account_name ?? "#{$subject->id}",
            default           => "#{$activity->subject_id}",
        };
    }

    public static function columnLabel(string $key): string
    {
        $map = [
            'customer_id'        => 'Customer',
            'supplier_id'        => 'Supplier',
            'employee_id'        => 'Employee',
            'category_id'        => 'Category',
            'currency_id'        => 'Currency',
            'account_id'         => 'Account',
            'bank_id'            => 'Bank',
            'required_by'        => 'Required Date',
            'delivery_date'      => 'Delivery Date',
            'order_date'         => 'Order Date',
            'payment_date'       => 'Payment Date',
            'invoice_date'       => 'Invoice Date',
            'due_date'           => 'Due Date',
            'issue_date'         => 'Issue Date',
            'receive_date'       => 'Receive Date',
            'actual_return_date' => 'Return Date',
            'expected_return_date'=> 'Expected Return',
            'invoice_reference'  => 'Invoice Ref',
            'invoice_number'     => 'Invoice Number',
            'order_code'         => 'Order Code',
            'sample_code'        => 'Sample Code',
            'report_number'      => 'Report Number',
            'status'             => 'Status',
            'remarks'            => 'Remarks',
            'amount'             => 'Amount',
            'total_amount'       => 'Total Amount',
            'amount_paid'        => 'Amount Paid',
            'amount_due'         => 'Amount Due',
            'priority_level'     => 'Priority',
            'product_name'       => 'Product Name',
            'customer_name'      => 'Customer Name',
            'employee_name'      => 'Employee Name',
            'bank_name'          => 'Bank Name',
            'account_name'       => 'Account Name',
            'currency_name'      => 'Currency Name',
            'currency_code'      => 'Currency Code',
            'exchange_rate'      => 'Exchange Rate',
            'received_fc'        => 'FC Received',
            'actual_pkr_received'=> 'PKR Received',
            'pkr_gain_loss'      => 'FX Gain/Loss (PKR)',
        ];

        return $map[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function changeSummary(Activity $activity): string
    {
        $attrs = $activity->properties->get('attributes', []);
        if (empty($attrs)) {
            return '';
        }
        $labels = array_map([self::class, 'columnLabel'], array_keys($attrs));
        $shown  = array_slice($labels, 0, 3);
        $extra  = count($labels) - 3;
        $text   = 'Updated ' . implode(', ', $shown);
        if ($extra > 0) {
            $text .= " +{$extra} more";
        }
        return $text;
    }
}

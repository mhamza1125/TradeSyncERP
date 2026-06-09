<?php

namespace Database\Seeders;

use App\Models\Defect;
use Illuminate\Database\Seeder;

class DefectsSeeder extends Seeder
{
    public function run(): void
    {
        $defects = [
            // A
            ['defect_name' => 'Arm Hole Wrinkle',          'severity' => 'major',    'corrective_action' => 'Open armhole and inner-side stitching and re-stitch to remove wrinkles.'],
            // B
            ['defect_name' => 'Back Pocket Position Uneven','severity' => 'major',    'corrective_action' => 'Remove pocket stitching and reattach the pocket at the correct position.'],
            ['defect_name' => 'Belt Loop Missing',          'severity' => 'major',    'corrective_action' => 'Open the belt or bottom seam and attach the missing belt loop.'],
            ['defect_name' => 'Belt Loop Uneven',           'severity' => 'minor',    'corrective_action' => 'Detach and reattach belt loops to achieve proper alignment.'],
            ['defect_name' => 'Barcode Position Incorrect', 'severity' => 'minor',    'corrective_action' => 'Remove and reapply the barcode label in the correct position.'],
            ['defect_name' => 'Bartack Missing',            'severity' => 'major',    'corrective_action' => 'Apply the missing bartack.'],
            ['defect_name' => 'Bottom Wrinkle',             'severity' => 'minor',    'corrective_action' => 'Open the bottom seam and re-stitch to remove wrinkles.'],
            ['defect_name' => 'Bottom Panel Uneven',        'severity' => 'major',    'corrective_action' => 'Open the bottom panel and re-stitch to achieve proper alignment.'],
            ['defect_name' => 'Booklet Sticker Missing',    'severity' => 'minor',    'corrective_action' => 'Apply the required booklet sticker.'],
            ['defect_name' => 'Booklet Sticker Damaged',    'severity' => 'minor',    'corrective_action' => 'Replace the damaged booklet sticker.'],
            // C
            ['defect_name' => 'Carton Barcode Sticker Missing', 'severity' => 'minor', 'corrective_action' => 'Apply the missing carton barcode sticker.'],
            ['defect_name' => 'Carton Sticker Missing',     'severity' => 'minor',    'corrective_action' => 'Apply the missing carton sticker.'],
            ['defect_name' => 'Cuff Wrinkle',               'severity' => 'minor',    'corrective_action' => 'Open cuff stitching and re-stitch to remove wrinkles.'],
            ['defect_name' => 'Cuff Open Stitches',         'severity' => 'major',    'corrective_action' => 'Re-stitch the affected cuff area.'],
            ['defect_name' => 'Color Marks',                'severity' => 'major',    'corrective_action' => 'Remove marks through finishing; replace the panel if required.'],
            ['defect_name' => 'Color Fading',               'severity' => 'major',    'corrective_action' => 'Assess dye lot; replace affected pieces or rework as per standard.'],
            ['defect_name' => 'Collar Wrinkle',             'severity' => 'minor',    'corrective_action' => 'Re-press collar; if persistent, re-stitch collar stand.'],
            ['defect_name' => 'Collar Uneven',              'severity' => 'major',    'corrective_action' => 'Open collar seam and re-attach at equal width on both sides.'],
            // F
            ['defect_name' => 'Fabric Damage',              'severity' => 'major',    'corrective_action' => 'Replace the damaged panel.'],
            ['defect_name' => 'Fabric Needle Holes',        'severity' => 'major',    'corrective_action' => 'Repair through ironing when possible; otherwise replace the panel.'],
            ['defect_name' => 'Front Wrinkle',              'severity' => 'minor',    'corrective_action' => 'Open the front panel and re-stitch to remove wrinkles.'],
            ['defect_name' => 'Front Zip Waviness',         'severity' => 'major',    'corrective_action' => 'Open zipper stitching and reattach the zipper correctly.'],
            ['defect_name' => 'Fraying Edges',              'severity' => 'minor',    'corrective_action' => 'Trim and seal fraying edges using approved finishing method.'],
            // H
            ['defect_name' => 'Hem Uneven',                 'severity' => 'major',    'corrective_action' => 'Open hem and re-stitch to achieve even width throughout.'],
            ['defect_name' => 'Hole in Fabric',             'severity' => 'critical', 'corrective_action' => 'Replace the affected panel or reject the piece.'],
            // L
            ['defect_name' => 'Label Missing',              'severity' => 'major',    'corrective_action' => 'Attach the required label in the correct position.'],
            ['defect_name' => 'Label Damaged',              'severity' => 'major',    'corrective_action' => 'Remove and replace the damaged label.'],
            ['defect_name' => 'Label Position Incorrect',   'severity' => 'minor',    'corrective_action' => 'Remove and reattach the label in the correct position.'],
            ['defect_name' => 'Leather Damage',             'severity' => 'major',    'corrective_action' => 'Repair if possible; otherwise replace the affected panel.'],
            ['defect_name' => 'Leather Wrinkle',            'severity' => 'minor',    'corrective_action' => 'Remove through ironing or replace the panel if required.'],
            ['defect_name' => 'Lining Uneven',              'severity' => 'minor',    'corrective_action' => 'Open lining seam and re-attach evenly.'],
            ['defect_name' => 'Loose Thread',               'severity' => 'minor',    'corrective_action' => 'Trim and secure all loose threads.'],
            // N
            ['defect_name' => 'Needle Hole Visible',        'severity' => 'major',    'corrective_action' => 'Press with steam to close needle holes; replace panel if unresolved.'],
            // O
            ['defect_name' => 'Open Stitches',              'severity' => 'major',    'corrective_action' => 'Re-stitch all open seams.'],
            ['defect_name' => 'Over Stitches',              'severity' => 'minor',    'corrective_action' => 'Remove excess stitching and repair affected areas.'],
            // P
            ['defect_name' => 'Printing Damage',            'severity' => 'major',    'corrective_action' => 'Repair the print if possible; otherwise reprint or replace the panel.'],
            ['defect_name' => 'Printing Peeling Off',       'severity' => 'major',    'corrective_action' => 'Remove defective printing and reprint.'],
            ['defect_name' => 'Printing Position Incorrect','severity' => 'minor',    'corrective_action' => 'Replace or reprint the affected panel.'],
            ['defect_name' => 'Pocket Uneven',              'severity' => 'major',    'corrective_action' => 'Remove pocket and reattach symmetrically.'],
            // R
            ['defect_name' => 'Rivets Missing',             'severity' => 'major',    'corrective_action' => 'Install the missing rivets at correct positions.'],
            ['defect_name' => 'Run-Off Stitching',          'severity' => 'minor',    'corrective_action' => 'Trim run-off stitching and secure seam ends properly.'],
            // S
            ['defect_name' => 'Sole Damage',                'severity' => 'major',    'corrective_action' => 'Replace the damaged sole.'],
            ['defect_name' => 'Sole Open',                  'severity' => 'major',    'corrective_action' => 'Repair sole separation using the approved process.'],
            ['defect_name' => 'Stitching Uneven',           'severity' => 'major',    'corrective_action' => 'Open and re-stitch the affected area.'],
            ['defect_name' => 'Stitching Skip',             'severity' => 'major',    'corrective_action' => 'Re-stitch the affected area to eliminate skipped stitches.'],
            ['defect_name' => 'Size Label Wrong',           'severity' => 'critical', 'corrective_action' => 'Remove incorrect size label and attach the correct one immediately.'],
            ['defect_name' => 'Snap Button Not Functioning','severity' => 'functional','corrective_action' => 'Replace the defective snap button.'],
            ['defect_name' => 'Seam Puckering',             'severity' => 'minor',    'corrective_action' => 'Steam press the seam; if unresolved, re-stitch with correct tension.'],
            // T
            ['defect_name' => 'Thread Color Wrong',         'severity' => 'major',    'corrective_action' => 'Remove and re-stitch with the correct thread color.'],
            // V
            ['defect_name' => 'Velcro Missing',             'severity' => 'major',    'corrective_action' => 'Attach the required Velcro.'],
            ['defect_name' => 'Velcro Open Stitches',       'severity' => 'major',    'corrective_action' => 'Re-stitch the affected Velcro area.'],
            ['defect_name' => 'Velcro Not Adhering',        'severity' => 'functional','corrective_action' => 'Replace the Velcro and ensure correct attachment.'],
            // W
            ['defect_name' => 'Wrong Size Label Attached',  'severity' => 'critical', 'corrective_action' => 'Remove the incorrect size label and attach the correct one.'],
            ['defect_name' => 'Wrong Barcode Sticker Applied','severity' => 'critical','corrective_action' => 'Remove the incorrect barcode sticker and apply the correct one.'],
            ['defect_name' => 'Wrong Color Dispatched',     'severity' => 'critical', 'corrective_action' => 'Segregate and replace with the correct color/shade.'],
            // Z
            ['defect_name' => 'Zip Damage',                 'severity' => 'major',    'corrective_action' => 'Replace the zipper.'],
            ['defect_name' => 'Zip Slider Missing',         'severity' => 'critical', 'corrective_action' => 'Install the required zipper slider.'],
            ['defect_name' => 'Zip Puller Missing',         'severity' => 'critical', 'corrective_action' => 'Install the required zipper puller.'],
            ['defect_name' => 'Zip Not Functioning',        'severity' => 'functional','corrective_action' => 'Replace the zipper and test functionality.'],
            ['defect_name' => 'Zip Teeth Missing',          'severity' => 'major',    'corrective_action' => 'Replace the zipper.'],
        ];

        foreach ($defects as $defect) {
            Defect::updateOrCreate(
                ['defect_name' => $defect['defect_name']],
                array_merge($defect, ['status' => true])
            );
        }
    }
}

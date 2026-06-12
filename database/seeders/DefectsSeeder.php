<?php

namespace Database\Seeders;

use App\Models\Defect;
use Illuminate\Database\Seeder;

class DefectsSeeder extends Seeder
{
    public function run(): void
    {
        $defects = [
            // ── Alphabet A ──────────────────────────────────────────────────────────
            ['defect_name' => 'Arm Hole Wrinkle',                     'severity' => 'major',      'corrective_action' => 'Open arm hole and inner side stitching & re-stitch to remove the wrinkle.'],

            // ── Alphabet B ──────────────────────────────────────────────────────────
            ['defect_name' => 'Belt Loop Uneven',                     'severity' => 'minor',      'corrective_action' => 'Open one loop and reattach both loops together with proper alignment.'],
            ['defect_name' => 'Back Pocket Position Uneven',          'severity' => 'major',      'corrective_action' => 'Open pocket stitching and reattach at the correct position.'],
            ['defect_name' => 'Belt Loop Missed',                     'severity' => 'major',      'corrective_action' => 'Open belt or bottom stitching and attach the missing belt loop.'],
            ['defect_name' => 'Barcode Position Uneven',              'severity' => 'minor',      'corrective_action' => 'Remove and reapply the barcode sticker at the correct position.'],
            ['defect_name' => 'Belt Loop Over Stitch',                'severity' => 'minor',      'corrective_action' => 'Open stitches on the affected fabric area.'],
            ['defect_name' => 'Bartack Missed',                       'severity' => 'major',      'corrective_action' => 'Add bartack.'],
            ['defect_name' => 'Back Panel Uneven',                    'severity' => 'major',      'corrective_action' => 'Open inner side and re-stitch the panel to the required position.'],
            ['defect_name' => 'Belly Leather',                        'severity' => 'minor',      'corrective_action' => 'Remove leather bellyness by ironing or change the panel (depends on error severity).'],
            ['defect_name' => 'Bottom Wrinkle',                       'severity' => 'minor',      'corrective_action' => 'Open the bottom panel and re-stitch to remove the wrinkle.'],
            ['defect_name' => 'Bottom Needle Holes Leather',          'severity' => 'major',      'corrective_action' => 'Repair by touching to avoid visible needle holes; replace the panel (depends on error severity).'],
            ['defect_name' => 'Bottom Needle Holes Fabric',           'severity' => 'major',      'corrective_action' => 'Repair by ironing to avoid visible needle holes; replace the panel (depends on error severity).'],
            ['defect_name' => 'Bancollar Needle Holes Fabric',        'severity' => 'major',      'corrective_action' => 'Repair by ironing to avoid visible needle holes; replace the bancollar (depends on error severity).'],
            ['defect_name' => 'Bancollar Needle Holes Leather',       'severity' => 'major',      'corrective_action' => 'Repair by touching to avoid visible needle holes; replace the bancollar (depends on error severity).'],
            ['defect_name' => 'Bancollar Needle Holes',               'severity' => 'major',      'corrective_action' => 'Rectify through ironing.'],
            ['defect_name' => 'Bancollar Mis Stitched',               'severity' => 'major',      'corrective_action' => 'Re-stitch to complete the stitching.'],
            ['defect_name' => 'Bottom Panel Uneven',                  'severity' => 'major',      'corrective_action' => 'Open bottom panel and re-stitch even panel.'],
            ['defect_name' => 'Box Pocket Flap Over',                 'severity' => 'minor',      'corrective_action' => 'Open pocket flap and re-stitch at the correct position.'],
            ['defect_name' => 'Box Pocket Flap Position Out',         'severity' => 'major',      'corrective_action' => 'Open pocket flap and re-stitch at the correct position.'],
            ['defect_name' => 'Box Pocket Flap Over (Differ Size)',   'severity' => 'major',      'corrective_action' => 'Open pocket flap and stitch a correct one.'],
            ['defect_name' => 'Bancollar Loose Stitches',             'severity' => 'major',      'corrective_action' => 'Open bancollar stitches, re-stitch; to avoid needle holes change bancollar (as per error nature).'],
            ['defect_name' => 'Bancollar Fabric Damage',              'severity' => 'major',      'corrective_action' => 'Open bancollar stitching and replace the bancollar.'],
            ['defect_name' => 'Bancollar Leather Damage',             'severity' => 'major',      'corrective_action' => 'Open bancollar stitching and replace the bancollar.'],
            ['defect_name' => 'Booklet Sticker Missed',               'severity' => 'minor',      'corrective_action' => 'Add booklet sticker.'],
            ['defect_name' => 'Booklet Sticker Damage',               'severity' => 'minor',      'corrective_action' => 'Remove damaged booklet sticker and add a new one.'],
            ['defect_name' => 'Booklet & Tags Assembly Differ',       'severity' => 'minor',      'corrective_action' => 'Assemble as per requirement on tech pack.'],
            ['defect_name' => 'Bancollar Hood Uneven',                'severity' => 'major',      'corrective_action' => 'Open bancollar stitching and re-stitch the bancollar hood to make it even.'],
            ['defect_name' => 'Bottom Hood Uneven',                   'severity' => 'major',      'corrective_action' => 'Open bottom hood seam and re-stitch the bottom to make it even.'],
            ['defect_name' => 'Bottom Over Stitch Fabric',            'severity' => 'minor',      'corrective_action' => 'Open bottom panel over stitching.'],
            ['defect_name' => 'Bottom Over Stitch Leather',           'severity' => 'major',      'corrective_action' => 'Open bottom stitching and change the bottom panel.'],
            ['defect_name' => 'Bancollar Over Stitch Fabric',         'severity' => 'minor',      'corrective_action' => 'Open the bancollar over stitching.'],
            ['defect_name' => 'Bancollar Over Stitch Leather',        'severity' => 'major',      'corrective_action' => 'Open the bancollar stitching and change bancollar.'],

            // ── Alphabet C ──────────────────────────────────────────────────────────
            ['defect_name' => 'Cuff Wrinkle',                         'severity' => 'minor',      'corrective_action' => 'Open cuff stitches and re-stitch to remove the wrinkle.'],
            ['defect_name' => 'Carton Barcode Sticker Missed',        'severity' => 'minor',      'corrective_action' => 'Apply missed barcode sticker.'],
            ['defect_name' => 'Cuff Hood Uneven Fabric',              'severity' => 'major',      'corrective_action' => 'Open cuff hood stitching and re-stitch to make it even.'],
            ['defect_name' => 'Carton Sticker Missed',                'severity' => 'minor',      'corrective_action' => 'Apply missed sticker.'],
            ['defect_name' => 'Cuff Open Stitches',                   'severity' => 'major',      'corrective_action' => 'Re-stitch the cuff.'],
            ['defect_name' => 'Cuff Needle Holes Leather',            'severity' => 'major',      'corrective_action' => 'Repair by touching to avoid visible needle holes; replace the cuff (depends on error severity).'],
            ['defect_name' => 'Cuff Needle Holes Fabric',             'severity' => 'major',      'corrective_action' => 'Repair by ironing to avoid visible needle holes; replace the cuff (depends on fabric).'],
            ['defect_name' => 'Ceiling Missed',                       'severity' => 'major',      'corrective_action' => 'Ceil the missing part.'],
            ['defect_name' => 'Ceiling Peel Off',                     'severity' => 'major',      'corrective_action' => 'Open ceiling and re-ceil the affected part.'],
            ['defect_name' => 'Ceiling Damage',                       'severity' => 'major',      'corrective_action' => 'Re-ceil the affected part.'],
            ['defect_name' => 'Color Marks',                          'severity' => 'major',      'corrective_action' => 'Remove color marks by finishing or change the panel (depends on error severity & fabric).'],

            // ── Alphabet D ──────────────────────────────────────────────────────────
            ['defect_name' => 'Dirty Booklet or Hang Tag',            'severity' => 'minor',      'corrective_action' => 'Remove dirty booklet or tag and add a new one.'],
            ['defect_name' => 'Damage Booklet or Hang Tag',           'severity' => 'minor',      'corrective_action' => 'Remove damaged booklet or hang tag and add a new one.'],
            ['defect_name' => 'Dirty Sole',                           'severity' => 'minor',      'corrective_action' => 'Finishing through brushing.'],
            ['defect_name' => 'Dust Stain/Mark',                      'severity' => 'minor',      'corrective_action' => 'Remove through finishing.'],

            // ── Alphabet E ──────────────────────────────────────────────────────────
            ['defect_name' => 'Embroidery Damage',                    'severity' => 'major',      'corrective_action' => 'Remove the embroidery panel and re-stitch with the correct one.'],
            ['defect_name' => 'Embossing Damage Fabric',              'severity' => 'major',      'corrective_action' => 'Open panel stitching and change the embossing panel.'],
            ['defect_name' => 'Embossing Damage Leather',             'severity' => 'major',      'corrective_action' => 'Open panel stitching and change the embossing panel or rectify by touching (depends on error severity).'],
            ['defect_name' => 'Extra Label Stitched',                 'severity' => 'minor',      'corrective_action' => 'Remove extra label.'],

            // ── Alphabet F ──────────────────────────────────────────────────────────
            ['defect_name' => 'Front Joint or Panel Uneven',          'severity' => 'major',      'corrective_action' => 'Open the stitching and re-stitch at the correct position or point.'],
            ['defect_name' => 'Fabric Matching Out',                  'severity' => 'major',      'corrective_action' => 'Remove the incorrect fabric and replace it with the correct fabric.'],
            ['defect_name' => 'Fade Printing',                        'severity' => 'major',      'corrective_action' => 'Remove printing and re-print the fade printing.'],
            ['defect_name' => 'Front Wrinkle',                        'severity' => 'minor',      'corrective_action' => 'Open the front and re-stitch to remove the wrinkle.'],
            ['defect_name' => 'Front Zip Flap Open Stitches',         'severity' => 'major',      'corrective_action' => 'Re-stitch the missing part.'],
            ['defect_name' => 'Fashion Damage',                       'severity' => 'major',      'corrective_action' => 'Open inner side and change the fashion.'],
            ['defect_name' => 'Fashion Needle Holes Leather',         'severity' => 'major',      'corrective_action' => 'Repair by touching to avoid visible needle holes; replace the panel (depends on error severity).'],
            ['defect_name' => 'Fashion Needle Holes Fabric',          'severity' => 'major',      'corrective_action' => 'Repair by ironing to avoid visible needle holes; replace the panel (depends on error severity & fabric).'],
            ['defect_name' => 'Fashion Pocket Position Out',          'severity' => 'major',      'corrective_action' => 'Open fashion pocket stitching and change the panel.'],
            ['defect_name' => 'Finishing',                            'severity' => 'minor',      'corrective_action' => 'Rectify through brushing.'],
            ['defect_name' => 'Fabric Damage',                        'severity' => 'major',      'corrective_action' => 'Change damaged panel.'],
            ['defect_name' => 'Fabric Needle Holes',                  'severity' => 'major',      'corrective_action' => 'Change damaged panel or rectify by ironing (depends on severity & fabric).'],
            ['defect_name' => 'Flap Printing Position Out',           'severity' => 'major',      'corrective_action' => 'Re-print to correct the issue or change the flap (as per error nature).'],
            ['defect_name' => 'Fade Embossing',                       'severity' => 'major',      'corrective_action' => 'Open embossing panel and change the embossing panel.'],
            ['defect_name' => 'Fabric Color Bleeding',                'severity' => 'major',      'corrective_action' => 'Change the affected panel.'],
            ['defect_name' => 'Front Zip Waviness',                   'severity' => 'major',      'corrective_action' => 'Open front zip stitching and re-stitch the zip.'],
            ['defect_name' => 'Finger Open Stitches',                 'severity' => 'major',      'corrective_action' => 'Open lining and re-stitch the finger open stitches.'],
            ['defect_name' => 'Fabric Edges Fraying Off',             'severity' => 'minor',      'corrective_action' => 'Untrim fabric edges or change the affected panel (as per error severity).'],
            ['defect_name' => 'Fashion Wrinkle',                      'severity' => 'minor',      'corrective_action' => 'Open fashion stitching and re-stitch fashion to remove the wrinkle.'],
            ['defect_name' => 'Fabric Over Heat',                     'severity' => 'major',      'corrective_action' => 'Open stitch and re-stitch with a new panel.'],

            // ── Alphabet G ──────────────────────────────────────────────────────────
            ['defect_name' => 'Gusset Uneven',                        'severity' => 'major',      'corrective_action' => 'Stitch the gusset with actual margin as per required position.'],
            ['defect_name' => 'Gloves Gusset Uneven',                 'severity' => 'major',      'corrective_action' => 'Open piping and re-stitch the gusset with actual margin as per required position.'],
            ['defect_name' => 'Gusset Damage',                        'severity' => 'major',      'corrective_action' => 'Open gusset stitching and replace the gusset.'],
            ['defect_name' => 'Gear Panel Damage',                    'severity' => 'major',      'corrective_action' => 'Open sole and lining stitching and replace the gear panel.'],
            ['defect_name' => 'Gear Panel Scratch',                   'severity' => 'minor',      'corrective_action' => 'Rectify by touching.'],
            ['defect_name' => 'Glue Marks',                           'severity' => 'minor',      'corrective_action' => 'Remove glue marks by finishing.'],

            // ── Alphabet H ──────────────────────────────────────────────────────────
            ['defect_name' => 'Handwoven (Hand Sewing)',              'severity' => 'major',      'corrective_action' => 'Open affected panel stitching to change the panel.'],
            ['defect_name' => 'Heat Transfer Marks',                  'severity' => 'major',      'corrective_action' => 'Open panel stitching and replace the panel.'],
            ['defect_name' => 'Hand Size Uneven',                     'severity' => 'minor',      'corrective_action' => 'Rectify through ironing if size length is within 0.5 cm; otherwise change the glove.'],
            ['defect_name' => 'Hanger Damage or Wrong Brand',         'severity' => 'minor',      'corrective_action' => 'Change hanger.'],

            // ── Alphabet I ──────────────────────────────────────────────────────────
            ['defect_name' => 'Inverted Label',                       'severity' => 'major',      'corrective_action' => 'Open inner side and change label position.'],
            ['defect_name' => 'Inverted Label (Stitched)',            'severity' => 'major',      'corrective_action' => 'Open label stitches and stitch a new label or correct the label position (depends on label quality).'],
            ['defect_name' => 'Iron Press Marks',                     'severity' => 'major',      'corrective_action' => 'Open panel stitching and replace the panel.'],

            // ── Alphabet J ──────────────────────────────────────────────────────────
            ['defect_name' => 'Jump Stitching',                       'severity' => 'major',      'corrective_action' => 'Open stitching and re-stitch the affected panel.'],

            // ── Alphabet L ──────────────────────────────────────────────────────────
            ['defect_name' => 'Leg Length Uneven',                    'severity' => 'major',      'corrective_action' => 'Open the bottom panel and re-stitch with the correct leg panel.'],
            ['defect_name' => 'Lining Back Printing Damage',          'severity' => 'major',      'corrective_action' => 'Remove damaged printing and apply new printing or change the panel (depends on error severity).'],
            ['defect_name' => 'Leather Color Fade',                   'severity' => 'major',      'corrective_action' => 'Apply wax to remove fadeness or change the panel (depends on error severity).'],
            ['defect_name' => 'Lining Damage',                        'severity' => 'major',      'corrective_action' => 'Replace the lining or affected panel (as per error nature).'],
            ['defect_name' => 'Leather Color Bleeding',               'severity' => 'major',      'corrective_action' => 'Change the affected panel.'],
            ['defect_name' => 'Leather Wax Uneven',                   'severity' => 'minor',      'corrective_action' => 'Perform wax process to remove unevenness.'],
            ['defect_name' => 'Leather Grain Uneven',                 'severity' => 'major',      'corrective_action' => 'Change the affected panel if error severity is major; otherwise acceptable.'],
            ['defect_name' => 'Leather Damage',                       'severity' => 'major',      'corrective_action' => 'Repair by touching or replace the panel (depends on error severity).'],
            ['defect_name' => 'Leather Vein',                         'severity' => 'major',      'corrective_action' => 'Change the affected panel if veins are on outer side; veins on inner side do not require panel change.'],
            ['defect_name' => 'Leather Wrinkle',                      'severity' => 'minor',      'corrective_action' => 'Change the affected panel or rectify by ironing (depends on error severity).'],
            ['defect_name' => 'Leather Spots',                        'severity' => 'major',      'corrective_action' => 'Change the affected panel.'],
            ['defect_name' => 'Leather Color Fade (Process)',         'severity' => 'major',      'corrective_action' => 'Remove leather fadeness by applying color process or change the panel (depends on error severity).'],
            ['defect_name' => 'Label Missed',                         'severity' => 'major',      'corrective_action' => 'Open stitches where label is required and stitch the required label.'],
            ['defect_name' => 'Label Over Stitch',                    'severity' => 'minor',      'corrective_action' => 'Open label stitches and stitch a new label.'],
            ['defect_name' => 'Label Damage',                         'severity' => 'minor',      'corrective_action' => 'Open label stitches and stitch a new label.'],
            ['defect_name' => 'Label Over Heat',                      'severity' => 'major',      'corrective_action' => 'Open label stitches and stitch a new label.'],
            ['defect_name' => 'Label Fade Printing',                  'severity' => 'minor',      'corrective_action' => 'Open label stitches and stitch a new label.'],
            ['defect_name' => 'Label Position Out or Side Variation', 'severity' => 'minor',      'corrective_action' => 'Open label stitches and stitch at the correct position.'],
            ['defect_name' => 'Leather Bubbles',                      'severity' => 'major',      'corrective_action' => 'Change the affected panel.'],
            ['defect_name' => 'Lining Tight',                         'severity' => 'minor',      'corrective_action' => 'Open lining stitching and re-stitch lining to remove tightness.'],
            ['defect_name' => 'Lining Damage (Open Stitching)',       'severity' => 'major',      'corrective_action' => 'Open lining stitching and replace the lining.'],
            ['defect_name' => 'Lining Wrinkle',                       'severity' => 'minor',      'corrective_action' => 'Open lining stitching and re-stitch to remove the wrinkle.'],
            ['defect_name' => 'Laces Damage',                         'severity' => 'minor',      'corrective_action' => 'Change the laces.'],
            ['defect_name' => 'Lamination Peeling Off',               'severity' => 'major',      'corrective_action' => 'Fix through heat transfer or change panel; if neither condition applies the piece/pair will be rejected (depends on error severity).'],

            // ── Alphabet M ──────────────────────────────────────────────────────────
            ['defect_name' => 'Mis Stitched',                         'severity' => 'major',      'corrective_action' => 'Re-stitch the missing part.'],
            ['defect_name' => 'Metal Logo Color Fade',                'severity' => 'minor',      'corrective_action' => 'Replace the metal logo.'],
            ['defect_name' => 'Machine Label Stitches',               'severity' => 'minor',      'corrective_action' => 'Remove machine label.'],

            // ── Alphabet O ──────────────────────────────────────────────────────────
            ['defect_name' => 'Open Stitches',                        'severity' => 'major',      'corrective_action' => 'Re-stitch open stitches.'],
            ['defect_name' => 'Over Stitches',                        'severity' => 'minor',      'corrective_action' => 'Open over stitches; to avoid needle holes change the panel (as per error nature).'],
            ['defect_name' => 'Over Fabric',                          'severity' => 'minor',      'corrective_action' => 'Untrim the over fabric threading.'],
            ['defect_name' => 'Over Lock Missed',                     'severity' => 'major',      'corrective_action' => 'Over-lock the missed panel.'],
            ['defect_name' => 'Over Printing Edges or Marks',         'severity' => 'minor',      'corrective_action' => 'Remove over printing edges or marks.'],

            // ── Alphabet P ──────────────────────────────────────────────────────────
            ['defect_name' => 'Printing Damage',                      'severity' => 'major',      'corrective_action' => 'Repair by touching or remove damaged printing and re-print (as per error severity).'],
            ['defect_name' => 'Printing Peeling Off',                 'severity' => 'major',      'corrective_action' => 'Remove printing and re-print.'],
            ['defect_name' => 'Pipping Over Stitch',                  'severity' => 'minor',      'corrective_action' => 'Open over stitches; to avoid needle holes change the piping (as per error nature).'],
            ['defect_name' => 'Pocket Wrong Printing',                'severity' => 'major',      'corrective_action' => 'Replace the pocket with correct printing.'],
            ['defect_name' => 'Poly Bag Barcode Sticker Missed',      'severity' => 'minor',      'corrective_action' => 'Apply missed barcode sticker.'],
            ['defect_name' => 'Printing Position Out',                'severity' => 'major',      'corrective_action' => 'Change printing panel.'],
            ['defect_name' => 'Printing Color Bleeding',              'severity' => 'major',      'corrective_action' => 'Change the printing panel.'],
            ['defect_name' => 'Pencil Marks',                         'severity' => 'minor',      'corrective_action' => 'Remove pencil marks by finishing.'],
            ['defect_name' => 'Pipping Damage',                       'severity' => 'major',      'corrective_action' => 'Open piping stitching and change the piping.'],
            ['defect_name' => 'Pipping Mis Stitched',                 'severity' => 'major',      'corrective_action' => 'Re-stitch piping to stitch the missing part.'],
            ['defect_name' => 'Printing Over Heat',                   'severity' => 'major',      'corrective_action' => 'Remove printing and reprint; otherwise change the panel (as per error nature).'],
            ['defect_name' => 'Protector Pocket Shorter',             'severity' => 'minor',      'corrective_action' => 'Open protector pocket stitching to change the pocket.'],
            ['defect_name' => 'Protector Pocket Tightness',           'severity' => 'minor',      'corrective_action' => 'Open protector pocket stitching to change the pocket.'],
            ['defect_name' => 'Protector Missed',                     'severity' => 'minor',      'corrective_action' => 'Add the protector.'],

            // ── Alphabet R ──────────────────────────────────────────────────────────
            ['defect_name' => 'Reflector Bubbling',                   'severity' => 'minor',      'corrective_action' => 'Remove reflector bubbling by heat process.'],
            ['defect_name' => 'Reflector Over Heat',                  'severity' => 'major',      'corrective_action' => 'Remove reflector over-heat panel and change the panel.'],
            ['defect_name' => 'Reflector Unevenness',                 'severity' => 'minor',      'corrective_action' => 'Change the reflector to remove unevenness.'],
            ['defect_name' => 'Rubbed Fabric',                        'severity' => 'major',      'corrective_action' => 'Open rubbed fabric panel stitching and replace the panel.'],
            ['defect_name' => 'Rusty Metal Logo',                     'severity' => 'major',      'corrective_action' => 'Replace the metal logo.'],
            ['defect_name' => 'Rusty Snap',                           'severity' => 'major',      'corrective_action' => 'Replace the snap.'],

            // ── Alphabet S ──────────────────────────────────────────────────────────
            ['defect_name' => 'Sleeve Printing Peeling Off',          'severity' => 'major',      'corrective_action' => 'Remove printing and reprint or change sleeve printing panel (depends on error severity).'],
            ['defect_name' => 'Sleeve Open Stitches',                 'severity' => 'major',      'corrective_action' => 'Open inner side and re-stitch the open stitches.'],
            ['defect_name' => 'Screen Label Scratch or Damage',       'severity' => 'major',      'corrective_action' => 'Open lining stitching and change screen label.'],
            ['defect_name' => 'Strap Color Fade',                     'severity' => 'major',      'corrective_action' => 'Change the strap.'],
            ['defect_name' => 'Strap Color Fade (Wax Treatment)',     'severity' => 'minor',      'corrective_action' => 'Apply wax to remove fadeness.'],
            ['defect_name' => 'Snap Damage',                          'severity' => 'major',      'corrective_action' => 'Replace with a new snap.'],
            ['defect_name' => 'Side Wrinkle',                         'severity' => 'minor',      'corrective_action' => 'Open inner side stitching and re-stitch to remove the wrinkle.'],
            ['defect_name' => 'Sole Open',                            'severity' => 'major',      'corrective_action' => 'Fill glue to rectify the sole gap.'],
            ['defect_name' => 'Sole Damage',                          'severity' => 'major',      'corrective_action' => 'Open damaged sole and fix a new one.'],
            ['defect_name' => 'Sole Scratches',                       'severity' => 'minor',      'corrective_action' => 'Perform buff process or change sole (depends on error severity).'],
            ['defect_name' => 'Shoe Height Uneven',                   'severity' => 'major',      'corrective_action' => 'Rectify through flap closure adjustment or open sole and lining stitching and re-stitch correct panel (as per error severity).'],
            ['defect_name' => 'Shoe Box Damage',                      'severity' => 'minor',      'corrective_action' => 'Change shoe box.'],
            ['defect_name' => 'Scary Fingers',                        'severity' => 'minor',      'corrective_action' => 'Rectify through ironing or open lining stitching and re-stitch to remove scaryness.'],
            ['defect_name' => 'Seam Puckering',                       'severity' => 'minor',      'corrective_action' => 'Open stitching and re-stitch the affected panel to remove the puckering.'],
            ['defect_name' => 'Stitching Uneven',                     'severity' => 'major',      'corrective_action' => 'Open uneven stitching and re-stitch the affected panel.'],
            ['defect_name' => 'Screen Label Upside Down',             'severity' => 'major',      'corrective_action' => 'Open lining and re-stitch screen label with correct position.'],
            ['defect_name' => 'Sleeve Over Stitch',                   'severity' => 'minor',      'corrective_action' => 'Open sleeve over stitches and re-stitch the sleeve.'],

            // ── Alphabet T ──────────────────────────────────────────────────────────
            ['defect_name' => 'TPR Logo Color Difference',            'severity' => 'major',      'corrective_action' => 'Change the TPR logo with required color.'],
            ['defect_name' => 'TPR Logo Damage',                      'severity' => 'major',      'corrective_action' => 'Change the TPR logo.'],
            ['defect_name' => 'TPR Logo Position Out',                'severity' => 'minor',      'corrective_action' => 'Open TPR logo stitches and re-stitch at the correct position.'],
            ['defect_name' => 'TPR Logo Missed',                      'severity' => 'major',      'corrective_action' => 'Add missed TPR logo.'],
            ['defect_name' => 'TPU Heel Damage',                      'severity' => 'major',      'corrective_action' => 'Open inner side stitching to replace the damaged TPU heel.'],
            ['defect_name' => 'TPU Heel Scratch',                     'severity' => 'minor',      'corrective_action' => 'Open inner side stitching to replace the TPU heel.'],
            ['defect_name' => 'Toe Slider Position Out',              'severity' => 'minor',      'corrective_action' => 'Open toe slider and adjust/fix at the correct position.'],
            ['defect_name' => 'Toe Slider Damage',                    'severity' => 'major',      'corrective_action' => 'Open toe slider and fix a new one.'],
            ['defect_name' => 'Touch Damage',                         'severity' => 'major',      'corrective_action' => 'Open inner side and replace the touch.'],
            ['defect_name' => 'Toe Slider Missed',                    'severity' => 'major',      'corrective_action' => 'Add toe slider.'],
            ['defect_name' => 'Threading',                            'severity' => 'minor',      'corrective_action' => 'Untrim the threading.'],
            ['defect_name' => 'Thumb Panel Damage',                   'severity' => 'major',      'corrective_action' => 'Open inner side stitching and change the damaged panel.'],
            ['defect_name' => 'Twisted Finger',                       'severity' => 'minor',      'corrective_action' => 'Rectify through ironing.'],
            ['defect_name' => 'Thumb Roundness Uneven',               'severity' => 'minor',      'corrective_action' => 'Rectify through ironing or open lining stitching and re-stitch to remove unevenness.'],
            ['defect_name' => 'Thumb Size Unevenness',                'severity' => 'minor',      'corrective_action' => 'Rectify through ironing or pairing (as per error severity).'],

            // ── Alphabet U ──────────────────────────────────────────────────────────
            ['defect_name' => 'Untrimmed Velcro',                     'severity' => 'minor',      'corrective_action' => 'Trim over velcro by trimmer.'],
            ['defect_name' => 'Unfolded Fabric',                      'severity' => 'major',      'corrective_action' => 'Open stitch and re-stitch unfolded part/panel.'],

            // ── Alphabet V ──────────────────────────────────────────────────────────
            ['defect_name' => 'Velcro Missed',                        'severity' => 'major',      'corrective_action' => 'Stitch required velcro.'],
            ['defect_name' => 'Velcro Open Stitches',                 'severity' => 'major',      'corrective_action' => 'Re-stitch open side.'],
            ['defect_name' => 'Velcro Mis Stitched',                  'severity' => 'major',      'corrective_action' => 'Re-stitch the mis-stitched side.'],
            ['defect_name' => 'Velcro Fraying Off',                   'severity' => 'minor',      'corrective_action' => 'Open velcro stitching and change the velcro.'],
            ['defect_name' => 'Velcro Edges Sharper',                 'severity' => 'minor',      'corrective_action' => 'Untrim velcro edges to rectify sharpness into rounded form.'],

            // ── Alphabet W ──────────────────────────────────────────────────────────
            ['defect_name' => 'Water Proofing Test Fail (Gloves & Jacket)', 'severity' => 'critical', 'corrective_action' => 'Open lining stitching to change the hipora.'],
            ['defect_name' => 'Water Proofing Test Fail (Shoes)',     'severity' => 'critical',   'corrective_action' => 'Open sole and lining stitching to change the hipora.'],
            ['defect_name' => 'Wrong Size Inner Attached',            'severity' => 'critical',   'corrective_action' => 'Remove the wrong size inner and attach the correct one.'],
            ['defect_name' => 'Wrong Size Label Stitched',            'severity' => 'critical',   'corrective_action' => 'Remove the wrong size label and stitch correct one.'],
            ['defect_name' => 'Wrong PO Label Stitched',              'severity' => 'major',      'corrective_action' => 'Remove the wrong PO label and stitch correct one.'],
            ['defect_name' => 'Wrong Barcode Sticker Sticked',        'severity' => 'critical',   'corrective_action' => 'Remove the wrong barcode sticker and stick a correct one.'],

            // ── Alphabet Z ──────────────────────────────────────────────────────────
            ['defect_name' => 'Zip Damage',                           'severity' => 'major',      'corrective_action' => 'Open zip stitching and replace the zip.'],
            ['defect_name' => 'Zip Tight',                            'severity' => 'major',      'corrective_action' => 'Open zip stitching and replace the zip or apply oil (depends on error severity).'],
            ['defect_name' => 'Zip Lock Damage',                      'severity' => 'major',      'corrective_action' => 'Remove damaged lock and fix a new one.'],
            ['defect_name' => 'Zip Lock Coming Off',                  'severity' => 'major',      'corrective_action' => 'Fix it or attach a new one.'],
            ['defect_name' => 'Zip Lock Missed',                      'severity' => 'major',      'corrective_action' => 'Add zip lock.'],
            ['defect_name' => 'Zip Slider Missed',                    'severity' => 'critical',   'corrective_action' => 'Add zip slider.'],
            ['defect_name' => 'Zip Slider Damage',                    'severity' => 'major',      'corrective_action' => 'Remove damaged slider and add a new one.'],
            ['defect_name' => 'Zip Puller Missed',                    'severity' => 'major',      'corrective_action' => 'Add zip puller.'],
            ['defect_name' => 'Zip Puller Not Qualitative',           'severity' => 'minor',      'corrective_action' => 'Replace zip puller.'],
        ];

        foreach ($defects as $defect) {
            Defect::updateOrCreate(
                ['defect_name' => $defect['defect_name']],
                array_merge($defect, ['status' => true])
            );
        }
    }
}

===================================================================================================================

PDF Export — Section Count Bug
The inspection has 34 sections attached, all completed (pass/fail, none pending). The export incorrectly shows 38 sections.

Find and remove the extra sections appearing in the export that are not actually attached to this inspection (likely orphaned/deleted/unused sections from the database).
These phantom sections are showing as "pending" — once the unused sections are removed, this should resolve itself.
Verify the final export section count matches exactly the 34 sections attached to the inspection.

PDF Export — Image "Upload" Label
Some images in the export incorrectly show an "upload" label. These are general/reference images and should have no label — leave the caption area empty for them.

Inspection Run Edit Page (/inspections/2/runs/2/edit)
1. AQL Sampling
Remove the "AQL Notes" field — it's redundant since the "Remarks" field already serves this purpose.
2. Carton Dimension and Weight
Current row layout is too cramped (Length, Width, Height, Gross Weight, Net Weight all squeezed into one row). Update to a two-row layout per entry:

Row 1: dimension/weight fields (Length, Width, Height, Gross Weight, Net Weight) — give these more width.
Row 2: Remarks and Attachment (moved here to free up space in row 1).

3. Factory Readiness Check

Add image attachment capability to each row, matching the pattern used in the "Container Condition" section.
Adjust table column widths — use the "Functional Test" section as the reference for correct column proportions.

4. Labels Check
Adjust table column widths to match the "Functional Test" section's proportions.
5. Loading Schedule & Timing

Add image attachment capability to each row, matching the "Container Condition" section.
Adjust table column widths to match the "Functional Test" section's proportions.

6. Order Quantity vs Packing List
Adjust table column widths to match the "Functional Test" section's proportions.
7. Overall Carton Condition
Adjust table column widths to match the "Functional Test" section's proportions.
8. Packing Check
Adjust table column widths to match the "Functional Test" section's proportions.
9. Preproduction Checklist
Adjust table column widths to match the "Functional Test" section's proportions.
10. Raw Material Check
Adjust table column widths to match the "Functional Test" section's proportions.
11. Selected Carton SI
Use Size and Color dropdowns populated with system data, following the same pattern as "AQL Sampling" → "Quantity Distribution by Variation."
12. Variations and Approved Samples
Adjust table column widths to match the "Functional Test" section's proportions.

Goal: Complete all items above thoroughly and ensure the module is stable and production-ready.

===================================================================================================================

1. AQL division update. There we will use the selected inspection run sample and click add It's table will addable row will be added where we can select color and size with the ordered quantity and define the aql before that so the testing inpsection quantity will be calculated

Like we will select the inspection level
Critical defects level. major defects aql level, minor defects aql level
And we will also add the number of pieces of that order and this will automatically tell us how much are needed to be inspected

It will pick up the sameple for which inspection run is created. It should allow us to add the variatins of that sample with color and sizes and summed up will be the total quantity. Based on that total quantity it should divide how much samples of each variation should be inspected

Example: variation 1: 50 samples. variation 2: 29 samples variation 3: 339 samples. Total: Summ up qty
Let's say if aql table says like 80 pcs needs to be inspected. It should divide that 80 or close to 80 pcs to each variations

YOu need to update the current aql calculateor in that inspection section as well as here
http://127.0.0.1:8000/tools/aql-calculator
It should reflect correct behaviour and follow the caculator in root directory named "AQL-Calculator" with three files. index.html with the view and possible options. script.js with the guidelines and style.css for styling. You can have your own styling but the functionaly should reflect the one in that AQL-Calculator folder. Use all those options and workflow
Also add there the functionality that this aql calculator not only gives the total samples to be checked for each range but as a separate page / module it should also gives out the samples to be inspected if we give them the sample variations quanitty. 

https://work.devshane.site/public/customer-invoices/create
PO / Invoice No: TS/12/(Texual invoice number)

Favorable Loan (Which will not be received back)

===================================================================================================================

3. Improve pics view in export pdf

=====================================================================================
Simple Final QC: 
General Header
With images of different sections along with some text where needed
Image grouping with some title and description

Size chart of the sample checked
having Sizes, Order qty, Checked Qty, Error ratio 
Another table with conclusion
Total, Checked, Error (Major, Minor)

Same pattern for different articles

At the bottom there is the conslusion page in texual form 

=====================================================================================
=====================================================================================
Higher Final QC: 
General information and Inspection details
Different sections with Images
Each image could have it's own text area for detailing
Each section will have it's Notes area
Ordered, Checked, Error Quantities, With Major / Minor errors
Total rectified, Total rejected details

Result summary page with results

At the end it has the page defining
Style Name + Color: Checked Qty, Error Qty, QC Result, Corrective Actions

They are also adding the Customer Comments

=====================================================================================
=====================================================================================

1. Voice command for input fields
2. Translator English to any language
3. Image inspection with AI
4. AQL Calculator Update
5. Change salary days to 31 Always
6. Add general details of company in invoice printing
7. Add bill / PO for the purchase of goods. It will be recorded in expense

===================================================================================================================

Hosting Administration Control Panel: 
http://www.tradesolutions.pk:8880
http://158.69.103.250:8880
https://alma.pakistaniclicks.com:8443 
Hosting Control Panel Username: tradesolutions.pk
Hosting Control Panel Password: je6ABg@hDxumz786s
DB/User: tradesolutions_pk
Pass: je6ABg@hDxumz786s

===================================================================================================================

=============================================================================================================
=============================================================================================================

1. On login it is showing the currently running inspections

2. On opening of that inspection it has a sidebar of the things to be tested. 
Each sections shows things checked / total Like (0/2) or (1/5)
Showing things to be added / checked in each category of testing

=============================================================================================================
=============================================================================================================
Inspection Type: Final QC

Current sidebar shows

Quantity & Sampling count (2/2 completed)
Selected Cartons SI (X/Y Completed)
Packing Check SI
Packing check (CE) SI
Cover Photo
Labels check (CE) SI
Textie - Sample conformity check
Marking check Si
Testile & leather - functional
File to review
Check measurements SI
Denim & Textile Defect
Finish Inspection

To be added
Techpack comparision (Just like the sample comparision)
Swatch comparision
After watch affect
Water proof testing

Add defects clasifications 
Critical / Major / Minor
 
Sort sections by name

===================================================================================================================
===================================================================================================================

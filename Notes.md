===================================================================================================================
===================================================================================================================
1. http://127.0.0.1:8000/inspections/8/runs/8/edit
On here for the inspection sections we can edit the images which works fine. But we cant' do that again withtout the page refresh. Fix that. If we edit an image and open it agin for veiwing or editing it dont' shows up. 

2. http://127.0.0.1:8000/inspections/8/runs/8/export-pdf
"Article Inspection Results Table" http://127.0.0.1:8000/inspection-sections/24/edit . For that inspection section when the size column in empty that should not be shown in the print view. as in the software the rule is implemented when the whole column of record is empty is is not shown. For reference you can see the inpsection section "Production Status"http://127.0.0.1:8000/inspection-sections/39/edit for print view.

3. WE added to functionality to skip the section in inspection run. http://127.0.0.1:8000/inspections/8/runs/8/edit but make it possible to unkip it again. We cant' do that if skipped. that skip section button should work as a toggle to skip an unskip.

======================================================================
========================  New Changes  ===============================
======================================================================

4. http://127.0.0.1:8000/inspections/create
Some inspections don't have any pass / fail it just gives the result like "Inline Inspection". It just tells the customer that's the progress. So In that inspection create / edit for "Overall Status" add "N/A" or similar option with the "Pending, Fail, Pass" dropdown.

5. http://127.0.0.1:8000/inspections/7/runs/7/export-pdf
%Complete, Qty Input, Status Notes in "Production Status" inspection  section is already optional but that is not always required. and in the pdf export teh column is shown there empty which don't looks good. 
I need you to tell me what to do. My prefered solution is to eliminate the columns "PRODUCTION STAGE % COMPLETE QTY / OUTPUT STATUS NOTES" that don't have any data for it's rows. Dont' implement that point just tell what you prefer. 

6. Image attached to the "Production Status" inspection section shows the lable of the production stage i.e. "AT CUTTING STAGE" That is not needed anymore with each of the image. you can just group the images under single header for "AT CUTTING STAGE" or any other. Don't mention that stage with every image in pdf export. 
http://127.0.0.1:8000/inspections/7/runs/7/export-pdf

7. Same goes for other inpspection sections which accepts the images No need to mention below the each image for what is it about. IT should be shown with it's header like 
# CHECKPOINT RESULT REMARKS
1 Color / shade matches approved sample N/A
Below it's images 
Don't mention the label "Color / shade matches approved sample" below each image
And for teh checkpoints when we use N/A option dont' show it's images on the pdf export. 

8. Attach the inspection section "http://127.0.0.1:8000/inspection-sections/21/edit" "variations_sample" to the inline inspection "http://127.0.0.1:8000/masters/inspection-types/3" in the seeder. 

9. There are two buttons with each inspection sectino in inspection runs "http://127.0.0.1:8000/inspections/7/runs/7/edit"
Save and Mark as completed. Add another button there For skipping entire section if that is not needed But with a confirmation msg if that buttion is clicked. That is neeed. As in some inspection some inspection sections are not always needeed. So we can just skip them from there rather than removing it entirely from here. http://127.0.0.1:8000/masters/inspection-types/10

You will also need to have some Mandatory / Optional or skipable select or toggle here 
http://127.0.0.1:8000/inspection-sections/5/edit where the inspection sections are available so we can just tell which sections can be skipped and which not like finish inspection. And that skip button will only appear for teh inspection sectiosn that can be skipped. 

10. http://127.0.0.1:8000/inspections/6
The inspection runs should also mention the sample aginst with the inspection run is running So we can knew from that page rather than opening up the next page to see the sample. Add accordian / toggle for teh view of the inspectino sections in that inspectin run. For example:
General Information
Done
Packing Check SI
Done
Carton Dimensions & Weight
Done

11. Just an example for above task #5 . In "Carton Dimention & Weight" there is "NET WT. (KG)" column which is not usually filled. So that column should not be visible if that is all empty.  

12. Just like "Variations vs Approved Sample" inspection type add make another inspection type "Sticker measurement" you can name it better. That is not the measurement like the cm or somethign. IT's just as checkpoint for stickers on the box. if the match or not. Good or bad etc. add some general checkpoints. We'll update later if needed. 
Here is the link to the "Variations vs Approved Sample" inspection section. 
http://127.0.0.1:8000/inspection-sections/21/edit

That new can be added via teh seeder and attach it to the inspection types which have attahced "Variations vs Approved Sample" inspection type

13. http://127.0.0.1:8000/masters/defects/create
Make the Form card full width. 

===================================================================================================================

Customer Invoice and payments Update

1. http://127.0.0.1:8000/customer-invoices/10/export-pdf

For export The visual should be improved. Add some bottom padding in the top header logo. 
Also remove that line in invoice header below the header "TradeSyncERP — Quality Control & ERP"
Invoice number and date are already mentioned in the header like this "INV-2024-00001 20 Jul 2026"
So remove the invoice no and date from the below header. Also the due date should not be visible in print. Invoice Number INV-2024-00001
Invoice Date 20 Jul 2026
Due Date 04 Aug 2026

2. http://127.0.0.1:8000/customer-payments/3/export-pdf

For payment export view Similarly add the bottom padding in top header logo. You can take invoice reference above in the header wehre the id of that payment is mentioned. That dosen't makes any sense you can remove that id #3 and put the invoice reference no there "Invoice Reference INV-PGU-2024-008"
Also add the two input fields in that receive payments. 
First is "W/H tax deduction". It will be deducted from the payment being received. It's normally 1% You can keep it filled up but editable.
2nd is "Remittance Charges". It will be deducted from the converted pkr amount.
Let's take an example. The customer has to pay us 500 USD. He paid that. 1% go to withholding tax deductino which is 1% so we actually received 450 USD. Then we converted to pkr let's say the pkr amount is 126000 PKR and the bank charges teh Remitttance charges we will input that let's say 6000. So In our account we generally got the 120000 PKR. 

3. http://127.0.0.1:8000/customer-invoices/create

For customer invoicing we input the invoice items like this
Invoice Items:
Each row = one inspection/service charge
#	Supplier	Inspection Type	PO / Invoice No	Date	Amount

But there are some customers for which we have fixed amounts montly. We dont' need to mention that all for those. They work with us at fixed fee monthly we will just write the inspection charges i.e. the amount. No need to mention that supplier, inspection type, po/invoice number. We can just use the date and amount. You can add some kind of toggle or checkbox to skip the supplier / inspection type / po/invocie no and that should not be printed for such invoices. 

===================================================================================================================

1. http://127.0.0.1:8000/tools/aql-calculator
The AQL Calculator module needs some rework: Currently it only works as a calculator but make it just like the other modules with all the standard files of list table view, create / edit pages, adn delete record. This is due to we can't always calculate everything atonce. So we can create some record and save it for later use also the export pdf functionality should also work like the standard pdf export of the software which displays the pdf first in new tab and then we can view or download it if needed. 
The thing is it will no longer be just used a calculator it will also have database table and the record will be saved there.  

===================================================================================================================

1. http://127.0.0.1:8000/customer-orders/create
Add supplier attahmnet here just like the customer. you'll add db migration and add a select optino here in customer order

2. In sample module
	Add the following fields: 
•	Company Stripe Number
•	Customer Stripe Number
These are normal text fields optional

3. Add the following sample reference categories:
•	From shipment
•	Gold seal
•	Red seal
•	Silver seal 

you can have enum and show them as select dropdown in samples

4. In sample movement we are attaching it to the respective movement but still add the following things
Add a field of ” order number it's just texual field
 and inspection type (Inline, Final QC, Re QC) ” 
 for which the samples are issued. Keep these both optional 
 You can pickup inspection tyeps from ehre. http://127.0.0.1:8000/masters/inspection-types adn show then as dropdown. 

5. Add purchase orders module just like the Expense
In which we will type and write things what's needed. how much needed means qty, price and paid / non paid etc. or any other relevant details if any. This is exactly expense but hte expenses are like a thing just happened. But this is firlst the pursase order is created then it's printed anyway, taken to whereever needed. things are bought and then the paymetn is made against it. The paymetn dont' go anywhere it's just the cash out like expense. You can keep its' status as paid / unpaid or partisally paid and its' payment when recorded could go to the expense you can create expense type of Purchase or something which will be linked to that purchase module. 
This is expense page: http://127.0.0.1:8000/expenses
Here are expense heads: http://127.0.0.1:8000/masters/expense-heads
You can add a expense head for that purchase. You'll need to update the seeder with that expense head with no parent category. It's itself the main expese head.


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

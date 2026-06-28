===================================================================================================================

1. http://127.0.0.1:8000/activities
Make these activities more meaningfull. Currently it shows the subject along with the record id. And changes only display the columnsn updated. Make it better and add another page to see the activity detals. Also make the Subject column more meaning full. For record updateon there should be two columns in it's view page could be in json form or other. IT should tell teh before and updated things. 
Also add activities cleanup ocassionally like montholy weekly yearly etc. Whichever suits the best. 

2. http://127.0.0.1:8000/customer-orders
Update it's print view and update the column name to require date not the delivery date. Also update the label ETD form it's add order and edit order page. As it is using hte ETD (Estimated time delivery / date). It will be require date. 
Also it has header like "Customer Orders Confidential" That is not required and unnecessary. 
Also the footer shows this "Customer Orders" that is not needed. 
Also fix the footer page count in all the pdf exports as is ti shouwing "Page 1 of 0". There should not be the zero. IT should display the total number of exported pages. 
And for footer content. That should be center aligned. 
For main header. Remove this date added "28 Jun 2026, 17:33" and also the text "TradeSyncERP Quality Control & ERP System". As we are only using the logo in the header. And center align the header logo

3. http://127.0.0.1:8000/customer-orders/2
Same main header and footer cleanup as explained in #2
This page view is showing the require date but there is no require date shown in the pdf export. Please review this pdf expoert and all the other so they matches with thier view page from which that pdf is being exported. So they match the labels and other details. 
Update the table label of "Product Name" to "Product Categorry" as teh caetegory is being selectd in creation / edition of order. Same label should be there as well. Also there is no unit you can remove that column form the view of table "Requested Items (1)"

4. http://127.0.0.1:8000/samples/7/edit
Fix error: Call to undefined relationship [testingParameters] on model [App\Models\Sample].
app\Http\Controllers\Operations\SampleController.php:150
$sample->load('variations.color', 'variations.size', 'testingParameters.parameter', 'attachments');

5. http://127.0.0.1:8000/movements/1/edit
This edit page view is not matching with the create page. Please update that according to the create page. 

6. http://127.0.0.1:8000/samples
Remove the priority form the samples. Also connect the starus with the sample movement. Keep only static two statuses like In testing (If it's pending sample movemtn is there.). If it dont' have any pending smaple movment (Means we didint 'received all teh sample moved back. it's pending.) then it's status will be Received (Or any other )
The thing is the sample status should be auto updated based on the smaple movemnt. Also sample movment also have the receiving back of moved sample. Means opening / closing or any other status for smaple movment. 

7. http://127.0.0.1:8000/samples/9/edit
Fix error: Call to undefined relationship [testingParameters] on model [App\Models\Sample].
app\Http\Controllers\Operations\SampleController.php:150
 $sample->load('variations.color', 'variations.size', 'testingParameters.parameter', 'attachments');

8. http://127.0.0.1:8000/customer-payments
Shuffle the position of "Customer" and "Invoice Ref" column. And make the "invoice ref" clickable so ti opens up the view page of that payment. Remove the letter icon form teh "Customer" column. and remove the link form the customer name. For referenct you can see this view columns. "http://127.0.0.1:8000/customer-invoices"

9. http://127.0.0.1:8000/masters/customers
Show the customer attached curency in that table view Currenlty is not shown. Fix that. 

10. http://127.0.0.1:8000/
If possible keep the sidebar scrolled to the position of the opened tab.

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

6. http://127.0.0.1:8000/inspection-sections
Here also update and display teh sectino record in alphatical order. Keep the same order just in view of that page arrange them alphabetically 

7. http://127.0.0.1:8000/inspections/3
Display export buttons here as well. curreently thay are only visible in hte edit pages. 
Here: http://127.0.0.1:8000/inspections/3/edit
And here: http://127.0.0.1:8000/inspections/3/runs/4/edit
Also add those export buttions to the view page.

===================================================================================================================

===================================================================================================================

https://work.devshane.site/public/customer-invoices/create
PO / Invoice No: TS/12/(Texual invoice number)

Improve customer order view 

Favorable Loan (Which will not be received back)

Evaluation paper (Created before inspection). 

===================================================================================================================
===================================================================================================================

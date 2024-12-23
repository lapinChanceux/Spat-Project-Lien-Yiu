# Spat-Project-Lean-Yiu

Login Credential
Username: Admin
Password: 

System Workflow
-----------------------------------------
Make an appointment
-----------------------------------------
1. Customer click a 'Booking' button and a form (bootstrap modal) will pop up.
2. Customer  insert details (Name, Car number, Choosen date and time, Car brand and model, Phone number and email).
3. The system will validate (query in appointments table) that the Customer  car number did not already make an appointment on the same day.
4. The system also will check if the slot is available for service based on the Customer  choice date and time. (only 2 car slot is available whithin one hour).
5. If both validation is success Customer information will be store into the database and a success modal will pop up indicate the booking is confirm.
6. Else a bootstrap modal will pop up notifiying the Customer . 
7. An conformation email also will be send to the Customer email address. (Future Implementation)

-------------------------------------------
Appointment Form Validation
-------------------------------------------
- Name = No numbers allow.
- Car Number = No space allow.
- Required user to select both car brand and model.
- Date = Today and the day before cannot be selected.
- Time = Only allow to select time between 9am to 5pm (working hour).
- Phone number and email are required to fill.

-------------------------------------------
View, Display and Cancel An Appointment
-------------------------------------------
1. Customer click a 'Check Booking' button and a form (bootstrap modal) will pop up.
2. Customer insert details (Name, Car number, Phone Number or Email).
3. The system will search the customer information and a view conformation modal will pop up displaying user info.
4. At here user can choose to cancel appointment by clicking the 'Cancel Appointment' button.
5. A check box will appear for the user to click to comfirm cancelation.
6. Customer click confirm cancelation.
7. A bootstrap modal will pop up notifying cancel is success.
8. An email would be send to the user to notify the cancelation of the appointment. (Future Implementation)

-------------------------------------------
Login
-------------------------------------------
1. User (Admin) click a 'login' button and a form (bootstrap modal) will pop up.
2. User (Admin) insert credential eg.. Username and Password
3. The system will validate if the username exists
4. If the Username exists the system will compare the hash password
5. Else a bootstrap modal will pop up notifiying the User an error occur.
6. Success of the validation will display user (Admin) to the dashboard page.
   
-------------------------------------------
Login Form Validation
-------------------------------------------
- Both Username and Password field are required

-------------------------------------------
Admin View, Update and Delete An Appointment
-------------------------------------------
View
1. Admin click the eye icon in table of admin-dashboard page
2. The system will query based on the selected appoinment ID selected by the admin.
3. If the query is success a bootstrap modal will pop up show the details of the appoinment.

Update 
1. Admin click the pen action icon in table of admin-dashboard page.
2. A bootstrap modal contain drop down will be display.
3. The Admin select (pending/on-service/completed).
4. The system will query the appoinmnet table in database by id and update based on the Admin selected option. 
5. The dashboard appointmnet table will be updated.

Delete
1. Admin click the dustbin icon in table of admin-dashboard page.
2. The system first will search matching data on service table based on the selected id (FK).
3. Next the system will search for the matching data on the appoinmnet table and delete the data based on its id (PK).
4. The dashboard table will be updated based on the recent data.


# Spat-Project-Lean-Yiu

System Workflow

Make an appointment

1. User click a 'Booking' button and a form (bootstrap modal) will pop up.
2. User insert details (Name, Car number, Choosen date and time, Car brand and model, Phone number and email).
3. The system will validate (query in appointments table) that the user car number did not already make an appointment on the same day.
4. The system also will check if the slot is available for service based on the user choice date and time. (only 2 car slot is available whithin one hour).
5. If both validation is success user information will be store into the database and a success modal will pop up indicate the booking is confirm.
6. An conformation email also will be send to the user email address.


Appointment Form Validation

- Name = No numbers allow.
- Car Number = No space allow.
- Required user to select both car brand and model.
- Date = Today and the day before cannot be selected.
- Time = Only allow to select time between 9am to 5pm (working hour).
- Phone number and email are required to fill.


View, Display and Cancel An Appointment

1. User click a 'Check Booking' button and a form (bootstrap modal) will pop up.
2. User insert details (Name, Car number, Phone Number or Email).
3. The system will search the user information and a view conformation modal will pop up displaying user info.
4. At here user can choose to cancel appointment by clicking the 'Cancel Appointment' button.
5. A check box will appear for the user to click to comfirm cancelation.
6. An email would be send to the user to notify the cancelation of the appointment.


TBC..

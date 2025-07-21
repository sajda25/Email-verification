# XKCD

This project is a PHP-based email verification system where users register using their email, receive a verification code, and subscribe to get a random XKCD comic every day. A CRON job fetches a random XKCD comic and sends it to all registered users every 24 hours.

---





## 📌 Features 

### 1️⃣ **Email Verification**
- Users enter their email in a form.
- A **6-digit numeric code** is generated and emailed to them.
- Users enter the code in the form to verify and register.
- Store the verified email in `registered_emails.txt`.

### 2️⃣ **Unsubscribe Mechanism**
- Emails should include an **unsubscribe link**.
- Clicking it will take user to the unsubscribe page.
- Users enter their email in a form.
- A **6-digit numeric code** is generated and emailed to them.
- Users enter the code to confirm unsubscription.

### 3️⃣ **XKCD Comic Subscription**
- Every 24 hours, cron job should:
  - Fetch data from `https://xkcd.com/[randomComicID]/info.0.json`.
  - Format it as **HTML (not JSON)**.
  - Send it via email to all registered users.









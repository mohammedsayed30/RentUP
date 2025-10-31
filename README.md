# 🧾 Laravel Backend Skill Test — Swagger, Sanctum, FCM, Pipeline


## ⚙️ Setup & Installation

Follow these steps to get the project running locally 👇

```bash
# 1️⃣ Clone the repository
git clone https://github.com/mohammedsayed30/RentUP.git
cd RentUP

# 2️⃣ Install PHP dependencies
composer install

# 3️⃣ Copy the example environment file
cp .env.example .env

# 4️⃣ Generate the application key
php artisan key:generate

# 5️⃣ Configure your environment
# Open .env and update these:
# Database credentials
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password


##########################################
- do not forget to run this  " php artisan queue:work " for background jobs 
###########################################


#create  project in Firebase first then add the following in your env
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service_account.json
FCM_FIREBASE_PROJECT_ID="your_project_id"   
FCM_ENDPOINT=https://fcm.googleapis.com/v1/projects/${FCM_FIREBASE_PROJECT_ID}/messages:send

####################################################################################################################
- you will find in public fcm-test/index.html & firebase-messaging-sw.js replace my credentials with yours and then
- visit this " http://127.0.0.1:8000/fcm-test/index.html" in the web and clik get to get the device token 
######################################################################################################################

# 6️⃣ Run migrations
php artisan migrate

# 7️⃣  Seed example data
php artisan db:seed

# 8️⃣ Serve the app
php artisan serve
#  Visit http://127.0.0.1:8000
--------------------------------------------------------------------------------------------------------------------------

##Firebase Cloud Messaging (FCM) Setup

# 1️⃣ Go to Firebase Console → Project Settings → Service Accounts
# 2️⃣ Click “Generate new private key” to download your service_account.json
# 3️⃣ Move it to: storage/app/firebase/service_account.json
# 4️⃣ Make sure .env points to that path:
        FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service_account.json
# 5️⃣ Generate a device token using a small HTML file (fcm-test/index.html)  //to use to create token and use it for push notifications
# 6️⃣ Register that token via device create API endpoint 

-----------------------------------------------------------------------------------------------------------------------------------

##Swagger API Docs

Generate and view your Swagger docs easily:

php artisan l5-swagger:generate

Then visit:

http://127.0.0.1:8000/api/documentation

to view the documentations of swagger

------------------------------------------------------------------------------------------------------------------------------------

##Runing Tests
- define the database connection in php uinit to MYSQL and name for the database but you have to create the database in MYSQL Server then:
- run this command :
    php artisan test
- You should see all the tests pass 

--------------------------------------------------------------------------------------------------------------------------------------
#register
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
-H "Content-Type: application/json" \
-d '{
  "name": "Sam",
  "email": "sam@example.com",
  "password": "secret123",
}'
#login
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
-H "Content-Type: application/json" \
-d '{
  "email": "sam@example.com",
  "password": "secret123",
}'

#current user
curl -X GET http://127.0.0.1:8000/api/v1/auth/me \
-H "Authorization: Bearer {TOKEN}"

#logout
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout \
-H "Authorization: Bearer {TOKEN}"

#create order

curl -X POST http://127.0.0.1:8000/api/v1/orders \
-H "Authorization: Bearer {TOKEN}" \
-H "Content-Type: application/json" \
-d '{
  "code": "ORD-1001", // you do not have to add this it wil be handled by the code
  "amount_decimal": "199.99",
}'

##get orders

curl -X GET http://127.0.0.1:8000/api/v1/orders \
-H "Authorization: Bearer {TOKEN}"

##get orders by filters

curl -X GET http://127.0.0.1:8000/api/v1/orders?min=20&max=100 \
-H "Authorization: Bearer {TOKEN}"

##get order by id

curl -X GET http://127.0.0.1:8000/api/v1/orders/1 \
-H "Authorization: Bearer {TOKEN}"

##update the status of orders

curl -X PATCH http://127.0.0.1:8000/api/v1/orders/1 \
-H "Authorization: Bearer {TOKEN}" \
-H "Content-Type: application/json" \
-d '{
  "status": "shipped"
}'

#Register Device Token
curl -X POST http://127.0.0.1:8000/api/v1/devices \
-H "Authorization: Bearer {TOKEN}" \
-H "Content-Type: application/json" \
-d '{
  "token": "your_fcm_token_here",   //that taken from firebase
  "platform": "android"
}'


❌ Delete Device Token
curl -X DELETE http://127.0.0.1:8000/api/v1/devices/1 \
-H "Authorization: Bearer {TOKEN}"

🚀 Send Notification for Order manually
curl -X POST http://127.0.0.1:8000/api/v1/orders/1/notify \
-H "Authorization: Bearer {TOKEN}"

##if your configurations correct you should a notification appeared with the order info and when you upate the status too












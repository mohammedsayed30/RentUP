importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

firebase.initializeApp({
  apiKey: "Your API KEY",
  authDomain: "rentup-fe9c0.firebaseapp.com",
  projectId: "YOUR PROJECT ID",
  storageBucket: "rentup-fe9c0.appspot.com",
  messagingSenderId: "SENDER ID",
  appId: "YOUR APP ID"
});

const messaging = firebase.messaging();

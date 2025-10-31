importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

firebase.initializeApp({
  apiKey: "AIzaSyCPKM9TzEC2meM2cM-V1xeR7dvVBrMvwHM",
  authDomain: "rentup-fe9c0.firebaseapp.com",
  projectId: "rentup-fe9c0",
  storageBucket: "rentup-fe9c0.appspot.com",
  messagingSenderId: "37191878677",
  appId: "1:37191878677:web:89a02d169bc18a54cae61c"
});

const messaging = firebase.messaging();

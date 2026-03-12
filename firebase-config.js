import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getFirestore, collection, addDoc, getDocs, deleteDoc, updateDoc, doc, query, orderBy } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";
import { getAuth, signInWithEmailAndPassword, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyBI3ILC9gaExU5ScxPDmw2AzAKCqNSfvOw",
    authDomain: "solvo-7ff26.firebaseapp.com",
    projectId: "solvo-7ff26",
    storageBucket: "solvo-7ff26.firebasestorage.app",
    messagingSenderId: "1031888612460",
    appId: "1:1031888612460:web:fa9bd69369009a4a7dfc49"
};

const app = initializeApp(firebaseConfig);
export const db = getFirestore(app);
export const auth = getAuth(app);

// CRUD Helpers
export const saveData = (col, data) => addDoc(collection(db, col), { ...data, createdAt: new Date() });
export const getData = async (col) => {
    const q = query(collection(db, col), orderBy("createdAt", "desc"));
    const snap = await getDocs(q);
    return snap.docs.map(d => ({ id: d.id, ...d.data() }));
};
export const deleteData = (col, id) => deleteDoc(doc(db, col, id));

// ADDED THIS EXPORT TO FIX YOUR ERROR
export const updateData = (col, id, data) => updateDoc(doc(db, col, id), data);
import { db } from './firebase-config.js';
import { collection, addDoc, getDocs, query, orderBy } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

// Save Data (For Admin Panel)
export const addData = async (colName, data) => {
    try {
        await addDoc(collection(db, colName), {
            ...data,
            timestamp: new Date()
        });
        return { success: true };
    } catch (e) {
        return { success: false, error: e };
    }
};

// Fetch Data (For Frontend)
export const fetchData = async (colName) => {
    const q = query(collection(db, colName), orderBy("timestamp", "desc"));
    const querySnapshot = await getDocs(q);
    let dataArr = [];
    querySnapshot.forEach((doc) => {
        dataArr.push({ id: doc.id, ...doc.data() });
    });
    return dataArr;
};
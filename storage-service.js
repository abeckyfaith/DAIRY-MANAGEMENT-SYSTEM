// Firebase Storage Service - Dairy Management System
// Uses Firebase Realtime Database for shared data across all devices

(function initApp() {
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyBnR2BsqaF8D5vitptAcD8sJbFPB2r5gJ8",
        authDomain: "dairy-management-system-c83d4.firebaseapp.com",
        databaseURL: "https://dairy-management-system-c83d4.firebaseio.com",
        projectId: "dairy-management-system-c83d4",
        storageBucket: "dairy-management-system-c83d4.firebasestorage.app",
        messagingSenderId: "494915249852",
        appId: "1:494915249852:web:8d149b312a019e66e7d2b9"
    };

    // Initialize Firebase
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    window.auth = firebase.auth();
    window.database = firebase.database();

    // Create default admin user if none exists
    initDefaultAdmin();
})();

// Create default admin if none exists
async function initDefaultAdmin() {
    try {
        // First, try to sign in silently - if already exists, do nothing
        const snapshot = await database.ref('users').orderByChild('email').equalTo('admin@dairy.com').once('value');
        if (!snapshot.exists()) {
            // User doesn't exist in DB, create them in Firebase Auth first
            try {
                const userCredential = await auth.createUserWithEmailAndPassword('admin@dairy.com', 'admin123');
                // Then create their data in the database
                await database.ref('users').push({
                    username: 'admin',
                    full_name: 'Administrator',
                    email: 'admin@dairy.com',
                    role: 'Admin',
                    phone: '',
                    created_at: firebase.database.ServerValue.TIMESTAMP
                });
                // Sign out after creating (user will login manually)
                await auth.signOut();
            } catch (e) {
                // If "email-already-exists" error, user is already registered in Auth
                if (e.code !== 'auth/email-already-in-use') {
                    console.log('Admin check:', e.message);
                }
            }
        }
    } catch (e) {
        console.log('Init error:', e.message);
    }
}

const DB = {
    prefix: 'dairy_',

    // Auth - async login
    async login(email, password) {
        try {
            await auth.signInWithEmailAndPassword(email, password);
            const userData = await this.getUserData();
            return { success: true, user: userData };
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, error: this.getAuthErrorMessage(error.code) };
        }
    },

    getAuthErrorMessage(code) {
        switch(code) {
            case 'auth/user-not-found':
                return 'No account found with this email';
            case 'auth/wrong-password':
                return 'Incorrect password';
            case 'auth/invalid-email':
                return 'Invalid email address';
            case 'auth/too-many-requests':
                return 'Too many attempts. Try again later';
            default:
                return 'Login failed. Please try again';
        }
    },

    logout() {
        auth.signOut();
    },

    getCurrentUser() {
        return JSON.parse(localStorage.getItem('dairy_currentUser'));
    },

    setCurrentUser(user) {
        localStorage.setItem('dairy_currentUser', JSON.stringify(user));
    },

    clearCurrentUser() {
        localStorage.removeItem('dairy_currentUser');
    },

    async getUserData() {
        const user = auth.currentUser;
        if (!user) return null;
        
        const snapshot = await database.ref('users').orderByChild('email').equalTo(user.email).once('value');
        if (snapshot.exists()) {
            let userData = null;
            snapshot.forEach(child => {
                userData = { id: child.key, ...child.val() };
            });
            return userData;
        }
        return null;
    },

    // Users CRUD
    async getUsers() {
        const snapshot = await database.ref('users').once('value');
        const users = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                users.push({ id: child.key, ...child.val() });
            });
        }
        return users;
    },

    async createUser(userData) {
        // Register with Firebase Auth
        try {
            const userCredential = await auth.createUserWithEmailAndPassword(userData.email, userData.password);
            const user = userCredential.user;
            
            // Save user data to database
            const userRef = database.ref('users').push();
            await userRef.set({
                username: userData.username,
                full_name: userData.full_name,
                email: userData.email,
                role: userData.role || 'Worker',
                phone: userData.phone || '',
                created_at: firebase.database.ServerValue.TIMESTAMP
            });
            
            return userRef.key;
        } catch (error) {
            console.error('Create user error:', error);
            throw error;
        }
    },

    async updateUser(id, userData) {
        await database.ref('users/' + id).update({
            ...userData,
            updated_at: firebase.database.ServerValue.TIMESTAMP
        });
    },

    async deleteUser(id) {
        await database.ref('users/' + id).remove();
    },

    // Animals
    async getAllAnimals() {
        const snapshot = await database.ref('animals').once('value');
        const animals = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                animals.push({ id: child.key, ...child.val() });
            });
        }
        animals.sort((a, b) => (b.created_at || 0) - (a.created_at || 0));
        return animals;
    },

    async createAnimal(data) {
        const ref = database.ref('animals').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    async updateAnimal(id, data) {
        await database.ref('animals/' + id).update({
            ...data,
            updated_at: firebase.database.ServerValue.TIMESTAMP
        });
    },

    async deleteAnimal(id) {
        await database.ref('animals/' + id).remove();
    },

    // Milk Production
    async getAllMilk() {
        const snapshot = await database.ref('milk_production').orderByChild('recording_date').once('value');
        const records = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        records.reverse();
        return records;
    },

    async createMilk(data) {
        const ref = database.ref('milk_production').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    // Health Records
    async getAllHealth() {
        const snapshot = await database.ref('health').once('value');
        const records = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        return records;
    },

    async createHealth(data) {
        const ref = database.ref('health').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    // Finance - Expenses
    async getAllExpenses() {
        const snapshot = await database.ref('expenses').once('value');
        const records = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        return records;
    },

    async createExpense(data) {
        const ref = database.ref('expenses').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    // Finance - Income/Sales
    async getAllIncome() {
        const snapshot = await database.ref('income').once('value');
        const records = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        return records;
    },

    async createIncome(data) {
        const ref = database.ref('income').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    // Products
    async getAllProducts() {
        const snapshot = await database.ref('products').once('value');
        const products = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                products.push({ id: child.key, ...child.val() });
            });
        }
        return products;
    },

    async createProduct(data) {
        const ref = database.ref('products').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    async updateProduct(id, data) {
        await database.ref('products/' + id).update(data);
    },

    async deleteProduct(id) {
        await database.ref('products/' + id).remove();
    },

    // Feed
    async getAllFeed() {
        const snapshot = await database.ref('feed').once('value');
        const items = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                items.push({ id: child.key, ...child.val() });
            });
        }
        return items;
    },

    async createFeed(data) {
        const ref = database.ref('feed').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    async updateFeed(id, data) {
        await database.ref('feed/' + id).update(data);
    },

    async deleteFeed(id) {
        await database.ref('feed/' + id).remove();
    },

    // Equipment
    async getAllEquipment() {
        const snapshot = await database.ref('equipment').once('value');
        const items = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                items.push({ id: child.key, ...child.val() });
            });
        }
        return items;
    },

    async createEquipment(data) {
        const ref = database.ref('equipment').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    async updateEquipment(id, data) {
        await database.ref('equipment/' + id).update(data);
    },

    async deleteEquipment(id) {
        await database.ref('equipment/' + id).remove();
    },

    // Reproduction
    async getAllReproduction() {
        const snapshot = await database.ref('reproduction').once('value');
        const items = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                items.push({ id: child.key, ...child.val() });
            });
        }
        return items;
    },

    async createReproduction(data) {
        const ref = database.ref('reproduction').push();
        await ref.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return ref.key;
    },

    // Dashboard Stats
    async getStats() {
        const stats = {
            total_animals: 0,
            total_milk: 0,
            total_products: 0,
            total_income: 0,
            total_expenses: 0
        };

        // Animals
        const animalsSnap = await database.ref('animals').once('value');
        if (animalsSnap.exists()) {
            stats.total_animals = animalsSnap.numChildren();
        }

        // Milk (last 30 records)
        const milkSnap = await database.ref('milk_production').limitToLast(30).once('value');
        if (milkSnap.exists()) {
            milkSnap.forEach(child => {
                stats.total_milk += parseFloat(child.val().amount_liters || 0);
            });
        }

        // Products
        const productsSnap = await database.ref('products').once('value');
        if (productsSnap.exists()) {
            stats.total_products = productsSnap.numChildren();
        }

        // Income
        const incomeSnap = await database.ref('income').once('value');
        if (incomeSnap.exists()) {
            incomeSnap.forEach(child => {
                stats.total_income += parseFloat(child.val().amount || 0);
            });
        }

        // Expenses
        const expenseSnap = await database.ref('expenses').once('value');
        if (expenseSnap.exists()) {
            expenseSnap.forEach(child => {
                stats.total_expenses += parseFloat(child.val().amount || 0);
            });
        }

        return stats;
    }
};

// Role checking
window.isAdmin = function() {
    const user = DB.getCurrentUser();
    return user && (user.role === 'Admin' || user.role === 'Manager');
};

window.isWorker = function() {
    const user = DB.getCurrentUser();
    return user && user.role === 'Worker';
};

// Export
window.DB = DB;
window.auth = auth;
window.database = database;

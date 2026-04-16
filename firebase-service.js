// Firebase Configuration for Dairy Management System
// Replace these values with your Firebase project config

const firebaseConfig = {
    apiKey: "AIzaSyBnR2BsqaF8D5vitptAcD8sJbFPB2r5gJ8",
    authDomain: "dairy-management-system-c83d4.firebaseapp.com",
    databaseURL: "https://dairy-management-system-c83d4.firebaseio.com",
    projectId: "dairy-management-system-c83d4",
    storageBucket: "dairy-management-system-c83d4.firebasestorage.app",
    messagingSenderId: "494915249852",
    appId: "1:494915249852:web:8d149b312a019e66e7d2b9",
    measurementId: "G-2CFTD49DKB"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
const database = firebase.database();

// ==================== AUTHENTICATION ====================

class AuthService {
    static async login(email, password) {
        try {
            await auth.signInWithEmailAndPassword(email, password);
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    static async register(email, password, userData) {
        try {
            const userCredential = await auth.createUserWithEmailAndPassword(email, password);
            const user = userCredential.user;
            
            // Save additional user data
            await database.ref('users/' + user.uid).set({
                username: userData.username,
                full_name: userData.full_name,
                email: email,
                role: userData.role || 'Worker',
                phone: userData.phone || '',
                created_at: firebase.database.ServerValue.TIMESTAMP
            });
            
            return { success: true, uid: user.uid };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    static logout() {
        auth.signOut();
    }

    static onAuthStateChange(callback) {
        auth.onAuthStateChanged(callback);
    }

    static getCurrentUser() {
        return auth.currentUser;
    }

    static async getUserData(uid) {
        const snapshot = await database.ref('users/' + uid).once('value');
        return snapshot.val();
    }
}

// ==================== ANIMALS SERVICE ====================

class AnimalsService {
    static async getAll(page = 1, limit = 10) {
        const snapshot = await database.ref('animals').once('value');
        let animals = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                animals.push({
                    id: child.key,
                    ...child.val()
                });
            });
        }
        
        // Sort by created_at descending
        animals.sort((a, b) => (b.created_at || 0) - (a.created_at || 0));
        
        // Pagination
        const total = animals.length;
        const start = (page - 1) * limit;
        const paginated = animals.slice(start, start + limit);
        
        return {
            animals: paginated,
            pagination: {
                page,
                limit,
                total,
                total_pages: Math.ceil(total / limit)
            }
        };
    }

    static async getById(id) {
        const snapshot = await database.ref('animals/' + id).once('value');
        if (snapshot.exists()) {
            return { id: snapshot.key, ...snapshot.val() };
        }
        return null;
    }

    static async create(data) {
        const newRef = database.ref('animals').push();
        const animalData = {
            tag_number: data.tag_number,
            breed_id: data.breed_id || null,
            breed_name: data.breed_name || '',
            birth_date: data.birth_date || null,
            gender: data.gender,
            weight: data.weight || null,
            status: data.status || 'Active',
            parent_sire_id: data.parent_sire_id || null,
            parent_dam_id: data.parent_dam_id || null,
            notes: data.notes || '',
            created_at: firebase.database.ServerValue.TIMESTAMP
        };
        
        await newRef.set(animalData);
        return { success: true, id: newRef.key };
    }

    static async update(id, data) {
        await database.ref('animals/' + id).update({
            ...data,
            updated_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true };
    }

    static async delete(id) {
        await database.ref('animals/' + id).remove();
        return { success: true };
    }
}

// ==================== MILK PRODUCTION SERVICE ====================

class MilkProductionService {
    static async getAll(page = 1, limit = 10) {
        const snapshot = await database.ref('milk_production').orderByChild('recording_date').once('value');
        let records = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        
        records.reverse();
        const total = records.length;
        const start = (page - 1) * limit;
        const paginated = records.slice(start, start + limit);
        
        return {
            records: paginated,
            pagination: { page, limit, total, total_pages: Math.ceil(total / limit) }
        };
    }

    static async create(data) {
        const newRef = database.ref('milk_production').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async getByAnimal(animalId) {
        const snapshot = await database.ref('milk_production')
            .orderByChild('animal_id')
            .equalTo(animalId)
            .once('value');
        
        let records = [];
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        return records;
    }

    static async getDailyTotal(date) {
        const snapshot = await database.ref('milk_production')
            .orderByChild('recording_date')
            .equalTo(date)
            .once('value');
        
        let total = 0;
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                total += parseFloat(child.val().amount_liters || 0);
            });
        }
        return total;
    }
}

// ==================== PRODUCTS SERVICE ====================

class ProductsService {
    static async getAll() {
        const snapshot = await database.ref('products').once('value');
        let products = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                products.push({ id: child.key, ...child.val() });
            });
        }
        return products;
    }

    static async create(data) {
        const newRef = database.ref('products').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async update(id, data) {
        await database.ref('products/' + id).update(data);
        return { success: true };
    }

    static async delete(id) {
        await database.ref('products/' + id).remove();
        return { success: true };
    }

    static async updateStock(id, quantity) {
        const ref = database.ref('products/' + id);
        const snapshot = await ref.once('value');
        const current = snapshot.val();
        const newStock = (current.stock_quantity || 0) + quantity;
        await ref.update({ stock_quantity: newStock });
        return { success: true, new_stock: newStock };
    }
}

// ==================== INVOICES SERVICE ====================

class InvoicesService {
    static async getAll() {
        const snapshot = await database.ref('invoices').orderByChild('invoice_date').once('value');
        let invoices = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                invoices.push({ id: child.key, ...child.val() });
            });
        }
        
        invoices.reverse();
        return invoices;
    }

    static async create(invoiceData, items) {
        const invoiceRef = database.ref('invoices').push();
        const invoice = {
            ...invoiceData,
            items: items,
            created_at: firebase.database.ServerValue.TIMESTAMP
        };
        
        await invoiceRef.set(invoice);
        
        // Update product stock
        for (const item of items) {
            await ProductsService.updateStock(item.product_id, -item.quantity);
        }
        
        return { success: true, id: invoiceRef.key };
    }

    static async getById(id) {
        const snapshot = await database.ref('invoices/' + id).once('value');
        if (snapshot.exists()) {
            return { id: snapshot.key, ...snapshot.val() };
        }
        return null;
    }
}

// ==================== FINANCE SERVICE ====================

class FinanceService {
    static async getIncome(startDate, endDate) {
        const snapshot = await database.ref('income').once('value');
        let records = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        
        if (startDate && endDate) {
            records = records.filter(r => 
                r.transaction_date >= startDate && r.transaction_date <= endDate
            );
        }
        
        return records;
    }

    static async getExpenses(startDate, endDate) {
        const snapshot = await database.ref('expenses').once('value');
        let records = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        
        if (startDate && endDate) {
            records = records.filter(r => 
                r.transaction_date >= startDate && r.transaction_date <= endDate
            );
        }
        
        return records;
    }

    static async addIncome(data) {
        const newRef = database.ref('income').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async addExpense(data) {
        const newRef = database.ref('expenses').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }
}

// ==================== HEALTH SERVICE ====================

class HealthService {
    static async getAll() {
        const snapshot = await database.ref('health').once('value');
        let records = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        
        return records;
    }

    static async create(data) {
        const newRef = database.ref('health').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }
}

// ==================== REPRODUCTION SERVICE ====================

class ReproductionService {
    static async getInseminations() {
        const snapshot = await database.ref('reproduction/inseminations').once('value');
        let records = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                records.push({ id: child.key, ...child.val() });
            });
        }
        
        return records;
    }

    static async recordInsemination(data) {
        const newRef = database.ref('reproduction/inseminations').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async recordPregnancy(data) {
        const newRef = database.ref('reproduction/pregnancies').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async recordCalving(data) {
        const newRef = database.ref('reproduction/calvings').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }
}

// ==================== FEED SERVICE ====================

class FeedService {
    static async getInventory() {
        const snapshot = await database.ref('feed/inventory').once('value');
        let items = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                items.push({ id: child.key, ...child.val() });
            });
        }
        
        return items;
    }

    static async updateInventory(id, data) {
        await database.ref('feed/inventory/' + id).update({
            ...data,
            last_updated: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true };
    }

    static async addToInventory(data) {
        const newRef = database.ref('feed/inventory').push();
        await newRef.set({
            ...data,
            last_updated: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }
}

// ==================== EQUIPMENT SERVICE ====================

class EquipmentService {
    static async getAll() {
        const snapshot = await database.ref('equipment').once('value');
        let items = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                items.push({ id: child.key, ...child.val() });
            });
        }
        
        return items;
    }

    static async create(data) {
        const newRef = database.ref('equipment').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async update(id, data) {
        await database.ref('equipment/' + id).update(data);
        return { success: true };
    }
}

// ==================== SUPPLIERS SERVICE ====================

class SuppliersService {
    static async getAll() {
        const snapshot = await database.ref('suppliers').once('value');
        let suppliers = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                suppliers.push({ id: child.key, ...child.val() });
            });
        }
        
        return suppliers;
    }

    static async create(data) {
        const newRef = database.ref('suppliers').push();
        await newRef.set({
            ...data,
            created_at: firebase.database.ServerValue.TIMESTAMP
        });
        return { success: true, id: newRef.key };
    }

    static async update(id, data) {
        await database.ref('suppliers/' + id).update(data);
        return { success: true };
    }
}

// ==================== BREEDS SERVICE ====================

class BreedsService {
    static async getAll() {
        const snapshot = await database.ref('breeds').once('value');
        let breeds = [];
        
        if (snapshot.exists()) {
            snapshot.forEach(child => {
                breeds.push({ id: child.key, ...child.val() });
            });
        }
        
        return breeds;
    }

    static async create(name) {
        const newRef = database.ref('breeds').push();
        await newRef.set({ name });
        return { success: true, id: newRef.key };
    }
}

// ==================== DASHBOARD STATS ====================

class DashboardService {
    static async getStats() {
        const stats = {
            total_animals: 0,
            total_milk_today: 0,
            total_products: 0,
            low_stock_items: 0,
            total_income: 0,
            total_expenses: 0
        };

        // Get total animals
        const animalsSnap = await database.ref('animals').once('value');
        if (animalsSnap.exists()) {
            stats.total_animals = animalsSnap.numChildren();
        }

        // Get today's milk
        const today = new Date().toISOString().split('T')[0];
        stats.total_milk_today = await MilkProductionService.getDailyTotal(today);

        // Get products
        const productsSnap = await database.ref('products').once('value');
        if (productsSnap.exists()) {
            stats.total_products = productsSnap.numChildren();
            
            productsSnap.forEach(child => {
                const product = child.val();
                if (product.stock_quantity <= product.low_stock_threshold) {
                    stats.low_stock_items++;
                }
            });
        }

        // Get finance
        const incomeSnap = await database.ref('income').once('value');
        if (incomeSnap.exists()) {
            incomeSnap.forEach(child => {
                stats.total_income += parseFloat(child.val().amount || 0);
            });
        }

        const expenseSnap = await database.ref('expenses').once('value');
        if (expenseSnap.exists()) {
            expenseSnap.forEach(child => {
                stats.total_expenses += parseFloat(child.val().amount || 0);
            });
        }

        return stats;
    }
}

// Export all services
window.AuthService = AuthService;
window.AnimalsService = AnimalsService;
window.MilkProductionService = MilkProductionService;
window.ProductsService = ProductsService;
window.InvoicesService = InvoicesService;
window.FinanceService = FinanceService;
window.HealthService = HealthService;
window.ReproductionService = ReproductionService;
window.FeedService = FeedService;
window.EquipmentService = EquipmentService;
window.SuppliersService = SuppliersService;
window.BreedsService = BreedsService;
window.DashboardService = DashboardService;

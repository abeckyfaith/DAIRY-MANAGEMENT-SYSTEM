// Local Storage Service - No Firebase needed

(function initDB() {
    const prefix = 'dairy_';

    // Create default admin if none exists
    const existingUsers = JSON.parse(localStorage.getItem(prefix + 'users')) || [];
    if (existingUsers.length === 0) {
        const defaultAdmin = {
            id: 'admin_' + Date.now(),
            username: 'admin',
            full_name: 'Administrator',
            email: 'admin@dairy.com',
            password: 'admin123',
            role: 'Admin',
            phone: '',
            created_at: new Date().toISOString()
        };
        localStorage.setItem(prefix + 'users', JSON.stringify([defaultAdmin]));
    }
})();

const DB = {
    prefix: 'dairy_',

    // Users
    getUsers() {
        return JSON.parse(localStorage.getItem(this.prefix + 'users')) || [];
    },

    saveUsers(users) {
        localStorage.setItem(this.prefix + 'users', JSON.stringify(users));
    },

    createUser(userData) {
        const users = this.getUsers();
        const id = Date.now().toString();
        users.push({
            id,
            ...userData,
            created_at: new Date().toISOString()
        });
        this.saveUsers(users);
        return id;
    },

    deleteUser(id) {
        const users = this.getUsers().filter(u => u.id !== id);
        this.saveUsers(users);
    },

    // Auth
    login(email, password) {
        const users = this.getUsers();
        const user = users.find(u => u.email === email && u.password === password);
        if (user) {
            localStorage.setItem(this.prefix + 'currentUser', JSON.stringify(user));
            return user;
        }
        return null;
    },

    logout() {
        localStorage.removeItem(this.prefix + 'currentUser');
    },

    getCurrentUser() {
        return JSON.parse(localStorage.getItem(this.prefix + 'currentUser'));
    },

    // Generic CRUD
    getAll(table) {
        return JSON.parse(localStorage.getItem(this.prefix + table)) || [];
    },

    saveAll(table, data) {
        localStorage.setItem(this.prefix + table, JSON.stringify(data));
    },

    create(table, item) {
        const items = this.getAll(table);
        const id = Date.now().toString();
        items.push({ id, ...item, created_at: new Date().toISOString() });
        this.saveAll(table, items);
        return id;
    },

    update(table, id, data) {
        const items = this.getAll(table);
        const index = items.findIndex(i => i.id === id);
        if (index !== -1) {
            items[index] = { ...items[index], ...data, updated_at: new Date().toISOString() };
            this.saveAll(table, items);
        }
    },

    delete(table, id) {
        const items = this.getAll(table).filter(i => i.id !== id);
        this.saveAll(table, items);
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

// Export for use
window.DB = DB;

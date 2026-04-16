// Local Storage Service - No Firebase needed

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

// Create default admin if none exists
if (DB.getUsers().length === 0) {
    DB.createUser({
        username: 'admin',
        full_name: 'Administrator',
        email: 'admin@dairy.com',
        password: 'admin123',
        role: 'Admin',
        phone: ''
    });
}

// Export for use
window.DB = DB;

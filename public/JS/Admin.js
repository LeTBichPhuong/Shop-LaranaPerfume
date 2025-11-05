// GLOBAL VARIABLES
let csrfToken = '';
let products = [];
let brands = [];
let users = [];
let orders = [];
let unifiedChart = null;
const PLACEHOLDER_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="150" height="150"%3E%3Crect width="150" height="150" fill="%23e0e0e0"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="%23999"%3EKHÔNG CÓ ẢNH%3C/text%3E%3C/svg%3E';

// lây csrfToken
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('CSRF Token:', token);
    if (!token) {
        console.warn('Không tìm thấy token CSRF trong tài liệu HTML.');
    }
    return token || '';
}

// giá
function formatCurrency(amount) {
    if (typeof amount === 'string' && amount.includes('₫')) {
        return amount;
    }
    
    const cleanAmount = String(amount).replace(/[^\d.]/g, '');
    const numAmount = parseFloat(cleanAmount || '0');
    
    if (isNaN(numAmount)) {
        return '0 ₫';
    }
    
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numAmount);
}

function sanitizeNumericString(value) {
    const raw = String(value == null ? '' : value);
    const cleaned = raw.replace(/[^\d.]/g, '');
    // If multiple dots exist (e.g., 1.234.567), keep only the first meaningful dot for decimal part
    const parts = cleaned.split('.');
    if (parts.length <= 2) return cleaned;
    return parts[0] + '.' + parts.slice(1).join('');
}

function getImagePath(imagePath) {
    if (!imagePath) {
        return PLACEHOLDER_IMAGE;
    }
    
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }

    return `/storage/${imagePath}`;
}

// Web Fetch wrapper
async function webFetch(url, options = {}, maxRetries = 1, retryCount = 0) {
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        ...(options.headers || {})
    };
    
    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }
    
    console.log('Gửi yêu cầu web:', url);

    const response = await fetch(url, {
        ...options,
        headers,
        credentials: 'same-origin'
    });

    if (!response.ok) {
        let errorPayload = null;
        const rawText = await response.text();
        try {
            errorPayload = JSON.parse(rawText);
        } catch (_) {
            // keep raw text
        }
        const composedMsg = (() => {
            if (errorPayload && errorPayload.message) {
                if (response.status === 422 && errorPayload.errors) {
                    const list = Object.entries(errorPayload.errors)
                        .flatMap(([, arr]) => Array.isArray(arr) ? arr : [String(arr)])
                        .join('\n');
                    return `${errorPayload.message}\n${list}`;
                }
                return errorPayload.message;
            }
            return rawText || `HTTP ${response.status}`;
        })();
        console.error(`Lỗi Web ${response.status}: ${url}`, composedMsg);
        
        if (response.status === 401 && retryCount < maxRetries) {
            console.warn('Phiên có thể đã hết hạn.');
            window.location.href = '/dang-nhap';
            return;
        }
        
        if (response.status === 500) {
            console.error('Lỗi máy chủ nội bộ.');
        }
        throw new Error(composedMsg);
    }
    return response;
}

// chỉnh định dạng tiếng
const statusMap = {
    'pending': { vi: 'Chờ xử lý' },
    'confirmed': { vi: 'Đã xác nhận' },
    'shipped': { vi: 'Đang giao hàng' },
    'delivered': { vi: 'Đã giao hàng' },
    'cancelled': { vi: 'Đơn hàng hủy' }
};

const serverStatusMap = {
    'pending': 'Chờ xử lý',
    'confirmed': 'Đã xác nhận',
    'shipped': 'Đang giao',
    'delivered': 'Đã giao',
    'cancelled': 'Đã hủy'
};

function getEnglishStatus(anyStatus) {
    const lowered = (anyStatus || '').toLowerCase();
    for (const [en, vn] of Object.entries(serverStatusMap)) {
        if (lowered === en || lowered === vn) {
            return en;
        }
    }
    return anyStatus;
}

function getServerStatus(enStatus) {
    return serverStatusMap[enStatus] || enStatus;
}

function getVietnameseStatus(enStatus) {
    return statusMap[enStatus]?.vi || enStatus;
}

// form chỉnh sửa
function injectEditModal() {
    if (document.getElementById('editFormOverlay')) return;

    const modalHTML = `
        <div id="editFormOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto;">
            <div style="max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; position: relative;">
                <button onclick="closeEditForm()" style="position: absolute; top: 15px; right: 15px; background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;">✖ Đóng</button>
                
                <h3 id="editModalTitle" style="margin-bottom: 20px;">Chỉnh sửa</h3>
                
                <!-- PRODUCT EDIT FORM -->
                <form id="editProductForm" style="display: none;">
                    <input type="hidden" id="editProductId">
                    
                    <div class="form-group mb-3">
                        <label for="editProductName">Tên sản phẩm *</label>
                        <input type="text" class="form-control" id="editProductName" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductBrand">Thương hiệu *</label>
                        <select class="form-select" id="editProductBrand" required>
                            <option value="">Chọn thương hiệu</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductPrice">Giá *</label>
                        <input type="text" class="form-control" id="editProductPrice" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductGender">Giới tính</label>
                        <select class="form-select" id="editProductGender">
                            <option value="nam">Nam</option>
                            <option value="khac">Khác</option>
                            <option value="unisex">Unisex</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductDescription">Mô tả</label>
                        <textarea class="form-control" id="editProductDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductStock">Tồn kho</label>
                        <input type="number" class="form-control" id="editProductStock" min="0" value="0">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editProductImage">Hình ảnh mới (để trống nếu không đổi)</label>
                        <input type="file" class="form-control" id="editProductImage" accept="image/*">
                        <div id="currentProductImage" class="mt-2"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Cập nhật sản phẩm</button>
                </form>

                <!-- BRAND EDIT FORM -->
                <form id="editBrandForm" style="display: none;">
                    <input type="hidden" id="editBrandId">
                    
                    <div class="form-group mb-3">
                        <label for="editBrandName">Tên thương hiệu *</label>
                        <input type="text" class="form-control" id="editBrandName" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editBrandLogo">Logo mới (để trống nếu không đổi)</label>
                        <input type="file" class="form-control" id="editBrandLogo" accept="image/*">
                        <div id="currentBrandLogo" class="mt-2"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Cập nhật thương hiệu</button>
                </form>

                <!-- USER EDIT FORM -->
                <form id="editUserForm" style="display: none;">
                    <input type="hidden" id="editUserId">
                    
                    <div class="form-group mb-3">
                        <label for="editUserName">Tên *</label>
                        <input type="text" class="form-control" id="editUserName" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editUserUsername">Username *</label>
                        <input type="text" class="form-control" id="editUserUsername" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editUserEmail">Email *</label>
                        <input type="email" class="form-control" id="editUserEmail" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editUserPassword">Mật khẩu mới (để trống nếu không đổi)</label>
                        <input type="password" class="form-control" id="editUserPassword" minlength="6">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="editUserRole">Vai trò</label>
                        <select class="form-select" id="editUserRole">
                            <option value="user">Người dùng</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Cập nhật người dùng</button>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    document.getElementById('editProductForm').addEventListener('submit', handleProductEdit);
    document.getElementById('editBrandForm').addEventListener('submit', handleBrandEdit);
    document.getElementById('editUserForm').addEventListener('submit', handleUserEdit);
}

// products
async function loadProducts() {
    try {
        const res = await webFetch('/admin/products');
        if (!res.ok) throw new Error('Không tải được sản phẩm');
        const responseData = await res.json();
        products = responseData.data || responseData;
        window.products = products;
        console.log('Loaded products:', products.length);
        renderProducts();
    } catch (err) {
        console.error('Lỗi khi tải sản phẩm:', err);
        alert('Không thể tải danh sách sản phẩm.');
    }
}

async function saveProduct(formData) {
    try {
        const res = await webFetch('/admin/products', {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(await res.text());
        await loadProducts();
        alert('Thêm sản phẩm thành công!');
    } catch (err) {
        alert('Lỗi khi thêm sản phẩm: ' + err.message);
    }
}

async function updateProduct(id, formData) {
    try {
        // Server route supports POST for update, do NOT override method
        if (!(formData instanceof FormData)) {
            const fd = new FormData();
            Object.entries(formData || {}).forEach(([k, v]) => fd.append(k, v));
            formData = fd;
        }
        const res = await webFetch(`/admin/products/${id}`, {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(await res.text());
        await loadProducts();
        closeEditForm();
        alert('Cập nhật sản phẩm thành công!');
    } catch (err) {
        alert('Lỗi khi cập nhật sản phẩm: ' + err.message);
    }
}


async function deleteProduct(id) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    try {
        await webFetch(`/admin/products/${id}`, { method: 'DELETE' });
        products = products.filter(p => p.id != id);
        renderProducts();
        alert('Xóa sản phẩm thành công!');
    } catch (err) {
        alert('Lỗi khi xóa sản phẩm: ' + err.message);
    }
}


function renderProducts() {
    const tableBody = document.getElementById('productTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (products.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Chưa có sản phẩm nào.</td></tr>';
        return;
    }
    
    products.forEach((p) => {
        const imagePath = getImagePath(p.image);
        
        tableBody.innerHTML += `
            <tr>
                <td>${p.id}</td>
                <td>${p.name || ''}</td>
                <td>${p.brand ? p.brand.name : 'Chưa có'}</td>
                <td>${formatCurrency(p.price)}</td>
                <td>${p.gender || ''}</td>
                <td>${(p.description || '').substring(0, 50)}${p.description && p.description.length > 50 ? '...' : ''}</td>
                <td>
                    <img src="${imagePath}" 
                         width="50" 
                         alt="${p.name || 'Sản phẩm'}" 
                         onerror="if(this.src!=='${PLACEHOLDER_IMAGE}')this.src='${PLACEHOLDER_IMAGE}'"
                         style="object-fit: cover; border-radius: 4px;">
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="showEditForm('product', ${p.id})">Sửa</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${p.id})">Xóa</button>
                </td>
            </tr>`;
    });
}

async function loadProductForEdit(id) {
    const product = products.find(p => p.id == id);
    if (!product) {
        alert('Không tìm thấy sản phẩm!');
        return;
    }

    document.getElementById('editModalTitle').textContent = `Sửa sản phẩm: ${product.name}`;
    document.getElementById('editProductForm').style.display = 'block';
    document.getElementById('editBrandForm').style.display = 'none';
    document.getElementById('editUserForm').style.display = 'none';

    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductName').value = product.name || '';
    document.getElementById('editProductPrice').value = product.price || '';
    document.getElementById('editProductGender').value = product.gender || 'unisex';
    document.getElementById('editProductDescription').value = product.description || '';
    document.getElementById('editProductStock').value = product.stock || 0;

    const brandSelect = document.getElementById('editProductBrand');
    brandSelect.innerHTML = '<option value="">Chọn thương hiệu</option>';
    brands.forEach(b => {
        const option = document.createElement('option');
        option.value = b.id;
        option.textContent = b.name;
        if (product.brand_id == b.id) option.selected = true;
        brandSelect.appendChild(option);
    });

    const currentImageDiv = document.getElementById('currentProductImage');
    if (product.image) {
        currentImageDiv.innerHTML = `
            <p>Ảnh hiện tại:</p>
            <img src="${getImagePath(product.image)}" 
                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;"
                 onerror="if(this.src!=='${PLACEHOLDER_IMAGE}')this.src='${PLACEHOLDER_IMAGE}'">
        `;
    } else {
        currentImageDiv.innerHTML = '<p class="text-muted">Chưa có ảnh</p>';
    }
}

async function handleProductEdit(e) {
    e.preventDefault();
    
    const id = document.getElementById('editProductId').value;
    const formData = new FormData();
    
    formData.append('name', document.getElementById('editProductName').value);
    formData.append('brand_id', document.getElementById('editProductBrand').value);
    formData.append('price', sanitizeNumericString(document.getElementById('editProductPrice').value));
    formData.append('gender', document.getElementById('editProductGender').value);
    formData.append('description', document.getElementById('editProductDescription').value);
    formData.append('stock', parseInt(document.getElementById('editProductStock').value || '0', 10));
    
    const imageFile = document.getElementById('editProductImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    await updateProduct(id, formData);
    closeEditForm();
}

// brands
async function loadBrands() {
    try {
        const res = await webFetch('/admin/brands');
        if (!res.ok) throw new Error('Không tải được thương hiệu');
        const responseData = await res.json();
        brands = responseData.data || responseData;
        window.brands = brands;
        console.log('Loaded brands:', brands.length);
        renderBrands();
    } catch (err) {
        console.error('Lỗi khi tải thương hiệu:', err);
        alert('Không thể tải danh sách thương hiệu.');
    }
}

async function saveBrand(fd) {
    try {
        const res = await webFetch('/admin/brands', {
            method: 'POST',
            body: fd
        });
        if (!res.ok) throw new Error(await res.text());
        await loadBrands();
        alert('Thêm thương hiệu thành công!');
    } catch (err) {
        alert('Lỗi khi thêm thương hiệu: ' + err.message);
    }
}

async function updateBrand(id, formData) {
    try {
        if (!(formData instanceof FormData)) {
            const fd = new FormData();
            Object.entries(formData || {}).forEach(([k, v]) => fd.append(k, v));
            formData = fd;
        }
        const res = await webFetch(`/admin/brands/${id}`, {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(await res.text());
        await loadBrands();
        closeEditForm();
        alert('Cập nhật thương hiệu thành công!');
    } catch (err) {
        alert('Lỗi khi cập nhật thương hiệu: ' + err.message);
    }
}


async function deleteBrand(id) {
    if (!confirm('Bạn có chắc muốn xóa thương hiệu này?')) return;
    try {
        await webFetch(`/admin/brands/${id}`, { method: 'DELETE' });
        brands = brands.filter(b => b.id != id);
        renderBrands();
        alert('Xóa thương hiệu thành công!');
    } catch (err) {
        alert('Lỗi khi xóa thương hiệu: ' + err.message);
    }
}

function renderBrands() {
    const tableBody = document.getElementById('brandTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (brands.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Chưa có thương hiệu nào.</td></tr>';
        return;
    }
    
    brands.forEach((b) => {
        const logoPath = getImagePath(b.logo);
        
        tableBody.innerHTML += `
            <tr>
                <td>${b.id}</td>
                <td>${b.name || ''}</td>
                <td>
                    <img src="${logoPath}" 
                         width="80" 
                         height="100px"
                         alt="${b.name || 'Thương hiệu'}" 
                         onerror="if(this.src!=='${PLACEHOLDER_IMAGE}')this.src='${PLACEHOLDER_IMAGE}'"
                         style="object-fit: cover; border-radius: 4px;">
                </td>
                <td>${b.products_count || 0}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="showEditForm('brand', ${b.id})">Sửa</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBrand(${b.id})">Xóa</button>
                </td>
            </tr>`;
    });
}

async function loadBrandForEdit(id) {
    const brand = brands.find(b => b.id == id);
    if (!brand) {
        alert('Không tìm thấy thương hiệu!');
        return;
    }

    document.getElementById('editModalTitle').textContent = `Sửa thương hiệu: ${brand.name}`;
    document.getElementById('editProductForm').style.display = 'none';
    document.getElementById('editBrandForm').style.display = 'block';
    document.getElementById('editUserForm').style.display = 'none';

    document.getElementById('editBrandId').value = brand.id;
    document.getElementById('editBrandName').value = brand.name || '';

    const currentLogoDiv = document.getElementById('currentBrandLogo');
    if (brand.logo) {
        currentLogoDiv.innerHTML = `
            <p>Logo hiện tại:</p>
            <img src="${getImagePath(brand.logo)}" 
                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;"
                 onerror="if(this.src!=='${PLACEHOLDER_IMAGE}')this.src='${PLACEHOLDER_IMAGE}'">
        `;
    } else {
        currentLogoDiv.innerHTML = '<p class="text-muted">Chưa có logo</p>';
    }
}

async function handleBrandEdit(e) {
    e.preventDefault();
    
    const id = document.getElementById('editBrandId').value;
    const formData = new FormData();
    
    formData.append('name', document.getElementById('editBrandName').value);
    
    const logoFile = document.getElementById('editBrandLogo').files[0];
    if (logoFile) {
        formData.append('logo', logoFile);
    }
    
    await updateBrand(id, formData);
    closeEditForm();
}

// user
async function loadUsers() {
    try {
        const res = await webFetch('/admin/users');
        if (!res.ok) throw new Error('Không tải được người dùng');
        const responseData = await res.json();
        users = responseData.data || responseData;
        window.users = users;
        console.log('Loaded users:', users.length);
        renderUsers();
    } catch (err) {
        console.error('Lỗi khi tải người dùng:', err);
        alert('Không thể tải danh sách người dùng.');
    }
}

async function saveUser(formData) {
    try {
        // Tạo người dùng dùng FormData để tương thích Laravel
        const res = await webFetch('/admin/users', {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(await res.text());
        await loadUsers();
        alert('Thêm người dùng thành công!');
    } catch (err) {
        alert('Lỗi khi thêm người dùng: ' + err.message);
    }
}

async function updateUser(id, userData) {
    try {
        // Cập nhật người dùng dùng POST theo route hiện tại (không override method)
        let formData = userData instanceof FormData ? userData : new FormData();
        if (!(userData instanceof FormData)) {
            Object.entries(userData || {}).forEach(([k, v]) => formData.append(k, v));
        }

        // PHP không parse multipart cho PUT, dùng POST + _method=PUT
        formData.append('_method', 'PUT');
        const res = await webFetch(`/admin/users/${id}`, {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(await res.text());
        await loadUsers();
        closeEditForm();
        alert('Cập nhật người dùng thành công!');
    } catch (err) {
        console.error(err);
        alert('Lỗi khi cập nhật người dùng: ' + err.message);
    }
}



async function deleteUser(id) {
    if (!confirm('Bạn có chắc muốn xóa người dùng này?')) return;
    try {
        await webFetch(`/admin/users/${id}`, { method: 'DELETE' });
        users = users.filter(u => u.id != id);
        renderUsers();
        alert('Xóa người dùng thành công!');
    } catch (err) {
        alert('Lỗi khi xóa người dùng: ' + err.message);
    }
}


function renderUsers() {
    const tableBody = document.getElementById('userTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Chưa có người dùng nào.</td></tr>';
        return;
    }
    
    users.forEach((u) => {
        const roleLabel = u.role === 'admin' ? 'Quản trị viên' : 'Người dùng';
        tableBody.innerHTML += `
            <tr>
                <td>${u.id}</td>
                <td>${u.name || u.username || ''}</td>
                <td>${u.email || ''}</td>
                <td>${u.orders_count || 0}</td>
                <td><span class="badge badge-${u.role === 'admin' ? 'danger' : 'secondary'}">${roleLabel}</span></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="showEditForm('user', ${u.id})">Sửa</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">Xóa</button>
                </td>
            </tr>`;
    });
}

async function loadUserForEdit(id) {
    const user = users.find(u => u.id == id);
    if (!user) {
        alert('Không tìm thấy người dùng!');
        return;
    }

    const displayName = user.username || user.email || '';
    document.getElementById('editModalTitle').textContent = `Sửa người dùng: ${displayName}`;
    document.getElementById('editProductForm').style.display = 'none';
    document.getElementById('editBrandForm').style.display = 'none';
    document.getElementById('editUserForm').style.display = 'block';

    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = user.username || '';
    document.getElementById('editUserUsername').value = user.username || '';
    document.getElementById('editUserEmail').value = user.email || '';
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserRole').value = user.role || 'user';
}

async function handleUserEdit(e) {
    e.preventDefault();

    const id = document.getElementById('editUserId').value;
    const formData = new FormData();
    const name = document.getElementById('editUserName').value;
    const username = document.getElementById('editUserUsername').value;
    const email = document.getElementById('editUserEmail').value;
    const role = document.getElementById('editUserRole').value;

    if (!name || !username || !email || !role) {
        alert('Vui lòng nhập đầy đủ: Tên, Username, Email, Vai trò.');
        return;
    }

    formData.append('name', name);
    formData.append('username', username);
    formData.append('email', email);
    formData.append('role', role);

    const password = document.getElementById('editUserPassword').value;
    if (password) {
        formData.append('password', password);
        formData.append('password_confirmation', password);
    }

    await updateUser(id, formData);
}

// order
async function loadOrders() {
    try {
        const res = await webFetch('/admin/orders');
        if (!res.ok) throw new Error('Không tải được đơn hàng');
        const responseData = await res.json();
        let rawOrders = responseData.data || responseData;
        
        orders = rawOrders.map(o => ({
            ...o,
            status: getEnglishStatus(o.status)
        }));
        window.orders = orders;
        console.log('Loaded orders:', orders.length);
        renderOrders();
    } catch (err) {
        console.error('Lỗi khi tải đơn hàng:', err);
        alert('Không thể tải danh sách đơn hàng.');
    }
}

async function updateOrderStatus(id, status) {
    console.log('Sending status to server:', status);
    try {
        const response = await webFetch(`/admin/orders/${id}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                status: getVietnameseStatus(status)
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Lỗi server:', errorText);
            throw new Error('Lỗi khi cập nhật trạng thái');
        }

        await loadOrders();
        alert('Cập nhật trạng thái thành công!');
    } catch (err) {
        console.error('Lỗi khi cập nhật trạng thái:', err);
        alert('Lỗi khi cập nhật trạng thái.');
    }
}

async function deleteOrder(id) {
    if (!confirm('Bạn có chắc muốn xóa đơn hàng này?')) return;
    
    try {
        await webFetch(`/admin/orders/${id}`, { 
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        await loadOrders();
        alert('Xóa đơn hàng thành công!');
    } catch (err) {
        console.error('Lỗi khi xóa đơn hàng:', err);
        alert('Lỗi khi xóa đơn hàng.');
    }
}

function renderOrders() {
    const tableBody = document.getElementById('orderTable');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (!orders || orders.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">Chưa có đơn hàng nào.</td>
            </tr>`;
        return;
    }

    orders.forEach((o) => {
        const customerName =
            (o.user && (o.user.name || o.user.username || o.user.email)) ||
            o.customer_name ||
            o.shipping_name ||
            o.billing_name ||
            o.recipient_name ||
            o.name ||
            o.email ||
            'Khách vãng lai';
        const statusVi = getVietnameseStatus(o.status);
        const currentServerStatus = getServerStatus(o.status);

        const statusClass =
            o.status === 'pending'
                ? 'bg-warning text-dark'
                : o.status === 'confirmed'
                ? 'bg-success text-white'
                : o.status === 'shipped'
                ? 'bg-info text-white'
                : o.status === 'delivered'
                ? 'bg-primary text-white'
                : o.status === 'cancelled'
                ? 'bg-danger text-white'
                : 'bg-secondary text-white';

        const totalFormatted = formatCurrency(o.total);

        const createdAt = o.created_at
            ? new Date(o.created_at).toLocaleDateString('vi-VN')
            : '';

        // Format hiển thị transaction_code
        const transactionCodeDisplay = o.transaction_code
            ? `<code>${o.transaction_code}</code>`
            : '<span class="text-muted">—</span>';

        const paymentLabel =
            o.payment_method === 'QR'
                ? '<span class="badge bg-info text-dark">QR</span>'
                : '<span class="badge bg-secondary text-white">COD</span>';

        tableBody.innerHTML += `
            <tr>
                <td>${o.id}</td>
                <td>${customerName}</td>
                <td>${totalFormatted}</td>
                <td><span class="badge ${statusClass} px-3 py-2">${statusVi}</span></td>
                <td>${paymentLabel}</td>
                <td>${transactionCodeDisplay}</td>
                <td>${createdAt}</td>
                <td>
                    <select onchange="updateOrderStatus(${o.id}, this.value)" class="form-select form-select-sm mb-1">
                        <option value="${currentServerStatus}" selected>${statusVi}</option>
                        <option value="chờ xử lý">Chờ xử lý</option>
                        <option value="đã xác nhận">Đã xác nhận</option>
                        <option value="đang giao">Đang giao</option> 
                        <option value="đã giao">Đã giao</option> 
                        <option value="đã hủy">Đã hủy</option> 
                    </select>
                    <button class="btn btn-sm btn-danger w-100" onclick="deleteOrder(${o.id})">
                        <i class="bi bi-trash"></i> Xóa
                    </button>
                </td>
            </tr>`;
    });
}

// thống kê và báo cáo
function setLast30Days() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
    const startInput = document.getElementById('dateStart');
    const endInput = document.getElementById('dateEnd');
    
    if (startInput) startInput.value = thirtyDaysAgo.toISOString().split('T')[0];
    if (endInput) endInput.value = today.toISOString().split('T')[0];
    
    loadDataByDateRange();
}

async function loadDataByDateRange() {
    const startDate = document.getElementById('dateStart')?.value;
    const endDate = document.getElementById('dateEnd')?.value;

    if (!startDate || !endDate) {
        console.warn('Không có ngày bắt đầu/kết thúc, bỏ qua load dữ liệu.');
        return;
    }

    try {
        updateStatCards(startDate, endDate);
        const chartData = buildLocalChartData(startDate, endDate);
        renderUnifiedChart(chartData);
        renderBreakdowns(orders, products);
        console.log('Đã load dữ liệu cho khoảng ngày:', startDate, 'đến', endDate);
    } catch (error) {
        console.error('Lỗi khi tải thống kê:', error);
    }
}

function updateStatCards(startDate, endDate) {
    const filteredOrders = orders.filter(o => {
        const date = new Date(o.created_at).toISOString().split('T')[0];
        return date >= startDate && date <= endDate;
    });

    const totalOrders = filteredOrders.length;
    
    const totalRevenue = filteredOrders.reduce((sum, o) => {
        const cleanTotal = String(o.total || '0').replace(/[^\d.]/g, '');
        const orderTotal = parseFloat(cleanTotal);
        return sum + (isNaN(orderTotal) ? 0 : orderTotal);
    }, 0);
    
    const totalProducts = products.length;

    const totalOrdersEl = document.getElementById('totalOrders');
    const totalRevenueEl = document.getElementById('totalRevenue');
    const totalProductsEl = document.getElementById('totalProducts');

    if (totalOrdersEl) totalOrdersEl.textContent = totalOrders;
    if (totalRevenueEl) totalRevenueEl.textContent = formatCurrency(totalRevenue);
    if (totalProductsEl) totalProductsEl.textContent = totalProducts;
}

function buildLocalChartData(startDate, endDate) {
    const grouped = {};

    orders.forEach(o => {
        const date = new Date(o.created_at).toISOString().split('T')[0];
        if (date < startDate || date > endDate) return;

        if (!grouped[date]) grouped[date] = { orders: 0, revenue: 0 };

        grouped[date].orders += 1;
        
        const cleanTotal = String(o.total || '0').replace(/[^\d.]/g, '');
        const orderTotal = parseFloat(cleanTotal);
        grouped[date].revenue += isNaN(orderTotal) ? 0 : orderTotal;
    });

    const labels = Object.keys(grouped).sort();
    const orders_data = labels.map(l => grouped[l].orders);
    const revenue_data = labels.map(l => grouped[l].revenue);

    return { labels, orders_data, revenue_data };
}

function renderUnifiedChart(data) {
    const ctx = document.getElementById('unifiedChart')?.getContext('2d');
    if (!ctx) return;

    if (unifiedChart) unifiedChart.destroy();

    unifiedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Đơn Hàng',
                    data: data.orders_data,
                    backgroundColor: '#6c757d'
                },
                {
                    label: 'Doanh Thu',
                    data: data.revenue_data,
                    backgroundColor: '#495057'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderBreakdowns(ordersData, productsData) {
    const ordersBreakdown = { 'chờ xử lý': 0, 'đã xác nhận': 0,'đang giao': 0, 'đã giao hàng': 0, 'đã hủy': 0 };
    const productsBreakdown = { 'nam': 0, 'khac': 0, 'unisex': 0 };

    ordersData.forEach(o => {
        const vnStatus = getVietnameseStatus(o.status || 'pending');
        if (ordersBreakdown[vnStatus] !== undefined) ordersBreakdown[vnStatus] += 1;
    });

    productsData.forEach(p => {
        const gender = p.gender || 'unisex';
        if (productsBreakdown[gender] !== undefined) productsBreakdown[gender] += 1;
    });

    const topProducts = {};
    ordersData.forEach(o => {
        if (o.items && Array.isArray(o.items)) {
            o.items.forEach(item => {
                const pid = item.product_id || item.id;
                const qty = item.quantity || 1;
                topProducts[pid] = (topProducts[pid] || 0) + qty;
            });
        }
    });

    const top5Products = Object.entries(topProducts)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5)
        .map(([pid, soldQty]) => {
            const product = productsData.find(p => p.id == pid);
            return {
                name: product ? product.name : `Sản phẩm ID ${pid}`,
                sold: soldQty
            };
        });

    const ordersBreakdownEl = document.getElementById('ordersBreakdown');
    if (ordersBreakdownEl) {
        ordersBreakdownEl.innerHTML = '';
        for (const [status, count] of Object.entries(ordersBreakdown)) {
            ordersBreakdownEl.innerHTML += `<li class="list-group-item">${status}: ${count}</li>`;
        }
    }

    const productsBreakdownEl = document.getElementById('productsBreakdown');
    if (productsBreakdownEl) {
        productsBreakdownEl.innerHTML = '';
        for (const [gender, count] of Object.entries(productsBreakdown)) {
            productsBreakdownEl.innerHTML += `<li class="list-group-item">${gender}: ${count}</li>`;
        }
    }

    const topProductsEl = document.getElementById('topProductsBreakdown');
    if (topProductsEl && top5Products.length > 0) {
        topProductsEl.innerHTML = '';
        top5Products.forEach((prod, index) => {
            topProductsEl.innerHTML += `<li class="list-group-item">Top ${index + 1}: ${prod.name} (${prod.sold} sản phẩm bán)</li>`;
        });
    } else if (topProductsEl) {
        topProductsEl.innerHTML = '<li class="list-group-item">Chưa có dữ liệu bán hàng.</li>';
    }
}

// form chỉnh
function showEditForm(type, id) {
    injectEditModal();
    
    if (type === 'product') {
        loadProductForEdit(id);
    } else if (type === 'brand') {
        loadBrandForEdit(id);
    } else if (type === 'user') {
        loadUserForEdit(id);
    }
    
    document.getElementById('editFormOverlay').style.display = 'block';
}

function closeEditForm() {
    const overlay = document.getElementById('editFormOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        document.getElementById('editProductForm')?.reset();
        document.getElementById('editBrandForm')?.reset();
        document.getElementById('editUserForm')?.reset();
    }
}

// thông báo
let readNotifications = new Set();
let unreadCount = 0;

async function checkAndShowNotifications() {
    const today = new Date().toISOString().split('T')[0];

    let orderNotifications = [];
    let productNotifications = [];

    orders.forEach(order => {
        if (order.status === 'pending' && order.created_at.split('T')[0] === today) {
            const notifId = `order-${order.id}`;
            const customerName = order.user?.name || 'không rõ';
            orderNotifications.push({
                id: notifId,
                message: `Có đơn hàng mới #${order.id} từ khách ${customerName} (Tổng: ${formatCurrency(order.total)})`
            });
        }
    });

    products.forEach(product => {
        if ((product.stock || 0) <= 0) {
            const notifId = `product-${product.id}`;
            productNotifications.push({
                id: notifId,
                message: `Sản phẩm "${product.name}" đã hết hàng (Cần bổ sung)`
            });
        }
    });

    const allNotifications = [...orderNotifications, ...productNotifications];

    const notificationList = document.getElementById('notificationList');
    const badge = document.getElementById('notificationBadge');
    if (!notificationList || !badge) return;

    notificationList.innerHTML = '';

    if (allNotifications.length > 0) {
        allNotifications.forEach(notif => {
            const li = document.createElement('li');
            li.className = 'notification-item';
            li.textContent = notif.message;
            li.dataset.id = notif.id;
            li.classList.add(readNotifications.has(notif.id) ? 'read' : 'unread');
            li.onclick = () => markAsRead(li);
            notificationList.appendChild(li);
        });
        unreadCount = allNotifications.filter(n => !readNotifications.has(n.id)).length;
    } else {
        const li = document.createElement('li');
        li.className = 'notification-item no-notifications';
        li.textContent = 'Không có thông báo mới';
        notificationList.appendChild(li);
        unreadCount = 0;
    }

    updateNotificationBadge();
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (!badge) return;
    if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        badge.style.display = 'inline-block';
    } else {
        badge.textContent = '';
        badge.style.display = 'none';
    }
}

function markAsRead(item) {
    if (item.classList.contains('unread')) {
        const notifId = item.dataset.id;
        readNotifications.add(notifId);
        item.classList.remove('unread');
        item.classList.add('read');
        unreadCount -= 1;
        updateNotificationBadge();
    }
}

function setupNotifications() {
    const bellIcon = document.getElementById('bell-icon');
    const notificationList = document.getElementById('notificationList');
    
    if (bellIcon && notificationList) {
        notificationList.style.maxHeight = '250px';
        notificationList.style.overflowY = 'auto';
        notificationList.style.overflowX = 'hidden'; 
        notificationList.classList.add('hidden');

        // Sự kiện click chuông
        bellIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationList.classList.toggle('hidden');
            if (!notificationList.classList.contains('hidden')) {
                checkAndShowNotifications(); 
                notificationList.classList.add('active');
                notificationList.scrollTop = 0;
            } else {
                notificationList.classList.remove('active');
            }
        });

        // Ẩn khi click ra ngoài
        document.addEventListener('click', (e) => {
            if (!bellIcon.contains(e.target) && !notificationList.contains(e.target)) {
                notificationList.classList.add('hidden');
                notificationList.classList.remove('active');
            }
        });
    }
    checkAndShowNotifications();
    setInterval(checkAndShowNotifications, 60 * 1000);
}

// thống kê
function stripDiacritics(str) {
    if (!str) return '';
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D');
}

function matchesFilter(scope, cellText, filterValue) {
    if (!filterValue || filterValue === 'all') return true;
    const norm = stripDiacritics(cellText).toLowerCase();
    const normFilter = stripDiacritics(filterValue).toLowerCase(); 

    if (scope === 'products') {
        if (filterValue === 'nam') return norm.includes('nam');
        if (filterValue === 'nu') return norm.includes('nu') || norm.includes('khac');
        if (filterValue === 'unisex') return norm.includes('unisex');
        return false;
    }

    if (scope === 'orders') {
        if (filterValue === 'chờ xử lý') return norm.includes(normFilter);
        if (filterValue === 'đã xác nhận') return norm.includes(normFilter);
        if (filterValue === 'đang giao') return norm.includes(normFilter);
        if (filterValue === 'đã giao ') return norm.includes(normFilter); 
        if (filterValue === 'đã hủy') return norm.includes(normFilter);
        return false;
    }

    return false;
}

// cài đặt
document.addEventListener('DOMContentLoaded', () => {
    const sidebarItems = document.querySelectorAll('.sidebar ul li[data-section]');
    sidebarItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = item.getAttribute('data-section');
            if (!sectionId) return;

            // Ẩn tất cả sections
            document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
            sidebarItems.forEach(li => li.classList.remove('active'));

            // Hiển thị section tương ứng
            const target = document.getElementById(sectionId);
            if (target) target.classList.add('active');

            // Active sidebar item
            item.classList.add('active');

            // Nếu là settings thì setup form
            if (sectionId === 'settings') {
                setupSettings();
            }
        });
    });

    const cogIcon = document.getElementById('cog-icon');
    if (cogIcon) {
        cogIcon.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Ẩn tất cả sections
            document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
            sidebarItems.forEach(li => li.classList.remove('active'));

            // Hiển thị settings
            const settingsSection = document.getElementById('settings');
            if (settingsSection) {
                settingsSection.classList.add('active');
                setupSettings();
            }

            // Đánh dấu sidebar active
            const settingsItem = document.querySelector('.sidebar ul li[data-section="settings"]');
            if (settingsItem) settingsItem.classList.add('active');
        });
    }
});

function setupSettings() {
    const togglePasswordBtn = document.getElementById('togglePasswordForm');
    const passwordForm = document.getElementById('passwordForm');
    const cancelPasswordBtn = document.getElementById('cancelPasswordForm');
    const exportBtn = document.getElementById('exportExcelBtn');
    const clearDataBtn = document.getElementById('clearDataBtnSettings');

    // Tránh gắn sự kiện lặp lại
    if (togglePasswordBtn && !togglePasswordBtn.dataset.bound) {
        togglePasswordBtn.dataset.bound = "true";
        togglePasswordBtn.addEventListener('click', () => {
            passwordForm.classList.toggle('hidden');
            if (!passwordForm.classList.contains('hidden')) {
                togglePasswordBtn.textContent = 'Ẩn Form Đổi Mật Khẩu';
                togglePasswordBtn.classList.replace('btn-save', 'btn-out');
            } else {
                togglePasswordBtn.textContent = 'Đổi Mật Khẩu Admin';
                togglePasswordBtn.classList.replace('btn-out', 'btn-save');
            }
        });
    }

    if (cancelPasswordBtn && !cancelPasswordBtn.dataset.bound) {
        cancelPasswordBtn.dataset.bound = "true";
        cancelPasswordBtn.addEventListener('click', () => {
            passwordForm.classList.add('hidden');
            togglePasswordBtn.textContent = 'Đổi Mật Khẩu Admin';
            togglePasswordBtn.classList.replace('btn-out', 'btn-save');
            passwordForm.reset();
        });
    }

    if (passwordForm && !passwordForm.dataset.bound) {
        passwordForm.dataset.bound = "true";
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentPass = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('password').value;
            const confirmPass = document.getElementById('confirmPassword').value;

            if (newPass !== confirmPass) {
                alert('Mật khẩu mới và xác nhận không khớp!');
                return;
            }

            if (newPass.length < 6) {
                alert('Mật khẩu mới phải ít nhất 6 ký tự!');
                return;
            }

            try {
                const response = await fetch('/admin/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ current_password: currentPass, password: newPass })
                });

                if (!response.ok) throw new Error('Lỗi cập nhật mật khẩu');
                alert('Cập nhật mật khẩu thành công!');
                passwordForm.reset();
                passwordForm.classList.add('hidden');
                togglePasswordBtn.textContent = 'Đổi Mật Khẩu Admin';
                togglePasswordBtn.classList.replace('btn-out', 'btn-save');
            } catch (err) {
                console.error('Lỗi đổi mật khẩu:', err);
                alert('Lỗi khi cập nhật mật khẩu. Kiểm tra mật khẩu hiện tại.');
            }
        });
    }

    if (exportBtn && !exportBtn.dataset.bound) {
        exportBtn.dataset.bound = "true";
        exportBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('/admin/export-orders', {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (!response.ok) throw new Error('Lỗi xuất file');

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `don-hang-${new Date().toISOString().split('T')[0]}.xlsx`;
                a.click();
                window.URL.revokeObjectURL(url);
                alert('Xuất file Excel thành công!');
            } catch (err) {
                console.error('Lỗi xuất Excel:', err);
                alert('Lỗi khi xuất file Excel.');
            }
        });
    }

    if (clearDataBtn && !clearDataBtn.dataset.bound) {
        clearDataBtn.dataset.bound = "true";
        clearDataBtn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa TẤT CẢ dữ liệu shop? Hành động này không thể hoàn tác!')) return;

            try {
                const response = await fetch('/admin/clear-data', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (!response.ok) throw new Error('Lỗi xóa dữ liệu');

                alert('Xóa dữ liệu thành công! Trang sẽ reload.');
                window.location.reload();
            } catch (err) {
                console.error('Lỗi xóa dữ liệu:', err);
                alert('Lỗi khi xóa dữ liệu.');
            }
        });
    }
}

// FORM TOGGLE
function setupToggle(buttonId, formId, addText, hideText) {
    const toggleBtn = document.getElementById(buttonId);
    const form = document.getElementById(formId);

    if (!toggleBtn || !form) return;

    toggleBtn.addEventListener('click', () => {
        const isHidden = form.style.display === 'none' || form.style.display === '';

        if (isHidden) {
            form.style.display = 'block';
            toggleBtn.innerHTML = `<i class="bi bi-dash-circle"></i> ${hideText}`;
            toggleBtn.classList.replace('btn-success', 'btn-danger');
        } else {
            form.style.display = 'none';
            toggleBtn.innerHTML = `<i class="bi bi-plus-circle"></i> ${addText}`;
            toggleBtn.classList.replace('btn-danger', 'btn-success');
        }
    });
}

// sidebar
document.addEventListener('DOMContentLoaded', async function() {
    csrfToken = getCsrfToken();

    // Load user info
    async function loadUser() {
        try {
            const res = await webFetch('/tai-khoan');
            // Trả về HTML khi không có API JSON -> bỏ qua parse
            const contentType = res.headers.get('Content-Type') || '';
            let displayName = 'Quản trị viên';
            if (!contentType.includes('application/json')) {
                const html = await res.text();
                try {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const pTags = Array.from(doc.querySelectorAll('p'));
                    const nameP = pTags.find(p => /\bTên\s*:/.test(p.textContent));
                    if (nameP) {
                        const text = nameP.textContent || '';
                        const parts = text.split(':');
                        if (parts.length > 1) displayName = parts[1].trim();
                    }
                } catch (_) { /* ignore */ }
            } else {
                const user = await res.json();
                displayName = user.name || user.username || displayName;
            }
            const userInfoEl = document.getElementById('userInfo');
            if (userInfoEl) {
                userInfoEl.textContent = displayName;
            }
        } catch (err) {
            console.error('Lỗi khi tải thông tin người dùng:', err);
        }
    }
    await loadUser();

    // Logout
    const userIcon = document.getElementById('user-icon');
    const userDropdown = document.getElementById('userDropdown');
    if (userIcon && userDropdown) {
        userIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            userIcon.classList.toggle('active');
        });
        document.addEventListener('click', () => userIcon.classList.remove('active'));
    }

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                try {
                    await webFetch('/dang-xuat', { method: 'POST' });
                    window.location.href = '/dang-nhap';
                } catch (err) {
                    console.error('Lỗi khi đăng xuất:', err);
                }
            }
        });
    }

    // Sidebar
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.querySelector(".sidebar");
    if (menuToggle && sidebar) {
        menuToggle.addEventListener("click", () => {
            sidebar.classList.toggle("active");
            menuToggle.classList.toggle("active");
        });
        document.addEventListener("click", (e) => {
            if (window.innerWidth <= 900) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove("active");
                    menuToggle.classList.remove("active");
                }
            }
        });
    }

    const sidebarItems = document.querySelectorAll('.sidebar ul li[data-section]');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function () {
            sidebarItems.forEach(li => li.classList.remove('active'));
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            this.classList.add('active');
            const sectionId = this.getAttribute('data-section');
            const targetSection = document.getElementById(sectionId);
            if (targetSection) targetSection.classList.add('active');
        });
    });

    // Setup toggles
    setupToggle('toggleProductFormBtn', 'productForm', 'Thêm sản phẩm', 'Ẩn form');
    setupToggle('toggleBrandFormBtn', 'brandForm', 'Thêm thương hiệu', 'Ẩn form');
    setupToggle('toggleUserFormBtn', 'userForm', 'Thêm người dùng', 'Ẩn form');

    // Form submissions - PRODUCTS
    const productForm = document.getElementById('productForm');
    if (productForm) {
        const toggleBtn = document.getElementById('toggleProductFormBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                setTimeout(() => {
                    const productCategorySelect = document.getElementById('productCategory');
                    if (productCategorySelect) {
                        productCategorySelect.innerHTML = '<option value="" disabled selected>-- Chọn thương hiệu --</option>';
                        brands.forEach(b => {
                            const option = document.createElement('option');
                            option.value = b.id;
                            option.textContent = b.name;
                            productCategorySelect.appendChild(option);
                        });
                    }
                }, 50);
            });
        }

        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('name', document.getElementById('productName').value);
            formData.append('brand_id', document.getElementById('productCategory').value);
            formData.append('price', sanitizeNumericString(document.getElementById('productPrice').value));
            formData.append('gender', document.getElementById('productGender').value || 'unisex');
            formData.append('description', document.getElementById('productDescription').value || '');
            formData.append('stock', 0);
            
            const imageFile = document.getElementById('productImage').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }
            
            await saveProduct(formData);
            productForm.reset();
        });
    }

    // Form submissions - BRANDS
    const brandForm = document.getElementById('brandForm');
    if (brandForm) {
        brandForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('name', document.getElementById('brandName').value);
            
            const logoFile = document.getElementById('brandLogo').files[0];
            if (logoFile) {
                formData.append('logo', logoFile);
            }
            
            await saveBrand(formData);
            brandForm.reset();
        });
    }

    // Form submissions - USERS
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fd = new FormData();
            fd.append('name', document.getElementById('userName').value);
            fd.append('username', document.getElementById('userUsername')?.value || document.getElementById('userName').value);
            fd.append('email', document.getElementById('userEmail').value);
            const pw = document.getElementById('userPassword').value;
            fd.append('password', pw);
            fd.append('password_confirmation', pw);
            fd.append('role', document.getElementById('userRole').value);

            await saveUser(fd);
            userForm.reset();
        });
    }

    // Filters
    document.querySelectorAll('.filter-option').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const filterValue = this.getAttribute('data-value');

            let scope = 'unknown';
            const section = this.closest('section');
            if (section) {
                if (section.id === 'products') scope = 'products';
                else if (section.id === 'orders') scope = 'orders';
            }

            let tableBodyId = null;
            let typeColIndex = null;
            if (scope === 'products') {
                tableBodyId = 'productTable';
                typeColIndex = 5;
            } else if (scope === 'orders') {
                tableBodyId = 'orderTable';
                typeColIndex = 4;
            }

            const tbody = document.getElementById(tableBodyId);
            if (!tbody) return;

            tbody.querySelectorAll('tr').forEach(row => {
                const cell = row.querySelector(`td:nth-child(${typeColIndex})`);
                const cellText = cell ? cell.textContent.trim() : '';
                const show = matchesFilter(scope, cellText, filterValue);
                row.style.display = show ? '' : 'none';
            });
        });
    });

    // load trang
    if (document.getElementById('sidebar')) {
        await Promise.all([
            loadProducts(),
            loadBrands(),
            loadUsers(),
            loadOrders()
        ]);
        setLast30Days();
        setupNotifications();
    }

});
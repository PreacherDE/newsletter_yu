<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produktverwaltung</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .main-container { display: flex; justify-content: center; padding: 50px 0; background-color: #f0f0f0; }
        .content { width: 66.66%; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .search-container { text-align: center; background-color: #d3d3d3; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .search-container input { width: 60%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .search-container button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .upload-container { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; }
        .upload-container button, .upload-container input[type="file"] { padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f4f4f4; }
        .container { display: flex; gap: 20px; }
        .table-container, .newsletter-container { padding: 20px; border-radius: 10px; background-color: #f9f9f9; }
        .table-container { flex: 2; }
        .newsletter-container { flex: 1; }
        .box { margin-bottom: 20px; }
        .box h2 { background-color: white; padding: 10px; border-radius: 10px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .category-group.blue { background-color: #ffffff; }
        .category-group.green { background-color: #d1d1d1; }
        .product-item:last-child { border-bottom: none; }
        .centered-content { display: flex; justify-content: center; align-items: center; flex-direction: column; }
        .newsletter-container h2 { text-align: left; margin-left: 0; }
        .newsletter-container ul { text-align: left; padding-left: 0; }
        .upload-container button {
            color: black;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="content">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Suche nach Produkten..." oninput="searchProducts()" onkeydown="checkEnter(event)">
                <button onclick="searchProducts()">Suchen</button>
                <div class="upload-container">
                    <input type="file" id="fileInput" accept=".json" onchange="importProducts(event)">
                    <button onclick="exportProducts()">Produkte speichern</button>
                </div>
            </div>
            <div class="container">
                <div class="table-container">
                    <div class="box">
                        <h2>Produkt hinzufügen</h2>
                        <form id="productForm" onsubmit="addProduct(event)">
                            <select id="productCategory" required style="padding: 5px;">
                                <option value="" disabled selected>Wählen Sie eine Kategorie</option>
                            </select>
                            <input type="text" id="productName" placeholder="Produktname" required style="padding: 5px;">
                            <input type="text" id="productNumber" placeholder="Artikelnummer" required style="padding: 5px;">
                            <button type="submit" style="padding: 5px;">Produkt hinzufügen</button>
                        </form>
                    </div>
                    <div class="box">
                        <h2>Kategorien verwalten</h2>
                        <form id="categoryForm">
                            <input type="text" id="newCategoryInput" placeholder="Neue Kategorie" required style="padding: 5px;">
                            <button type="button" onclick="addCategory()" style="padding: 5px;">Kategorie hinzufügen</button>
                            <ul id="categoryList"></ul>
                        </form>
                    </div>
                    <div class="box">
                        <h2>Produktliste</h2>
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th>Kategorie</th>
                                    <th>Produktname</th>
                                    <th>Artikelnummer</th>
                                    <th>Hinzugefügt am</th>
                                    <th>Newsletter Hinzugefügt am</th>
                                    <th>Im Newsletter</th>
                                    <th>Aktion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="newsletter-container">
                    <h2>Produkte im Newsletter</h2>
                    <table id="newsletterTable">
                        <thead>
                            <tr>
                                <th>Kategorie</th>
                                <th>Produktname</th>
                                <th>Artikelnummer</th>
                                <th>Hinzugefügt am</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funktion zum Laden der Kategorien in das Dropdown-Menü
function loadCategories() {
    fetch('database.php?action=getCategories')
        .then(response => response.json())
        .then(data => {
            const categoryDropdown = document.getElementById('productCategory');
            categoryDropdown.innerHTML = '<option value="" disabled selected>Wählen Sie eine Kategorie</option>'; // Dropdown zurücksetzen
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryDropdown.appendChild(option);
            });
        });
}

// Funktion zum Hinzufügen eines neuen Produkts
function addProduct(event) {
    event.preventDefault();

    const productName = document.getElementById('productName').value;
    const productNumber = document.getElementById('productNumber').value;
    const productCategory = document.getElementById('productCategory').value;

    fetch('database.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'addProduct',
            productName,
            productNumber,
            productCategory
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadProducts(); // Tabelle mit Produkten nach dem Hinzufügen neu laden
        } else {
            alert('Produkt konnte nicht hinzugefügt werden');
        }
    });
}

// Funktion zum Laden der Produktliste
function loadProducts() {
    fetch('database.php?action=getProducts')
        .then(response => response.json())
        .then(data => {
            const productTableBody = document.querySelector('#productTable tbody');
            productTableBody.innerHTML = ''; // Tabelle zurücksetzen
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><select class="categoryDropdown" data-id="${product.id}"></select></td>
                    <td>${product.productName}</td>
                    <td>${product.productNumber}</td>
                    <td>${product.createdAt}</td>
                    <td>${product.newsletterAddedAt || ''}</td>
                    <td><input type="checkbox" class="newsletterCheckbox" data-id="${product.id}" ${product.isInNewsletter ? 'checked' : ''}></td>
                    <td><button onclick="deleteProduct(${product.id})">Löschen</button></td>
                `;
                productTableBody.appendChild(row);

                // Kategorien im Dropdown innerhalb der Produktliste aktualisieren
                loadCategoriesForProduct(product.id);
            });
        });
}

// Funktion zum Laden der Kategorien für jedes Produkt
function loadCategoriesForProduct(productId) {
    fetch('database.php?action=getCategories')
        .then(response => response.json())
        .then(categories => {
            const categoryDropdown = document.querySelector(`.categoryDropdown[data-id="${productId}"]`);
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryDropdown.appendChild(option);
            });
        });
}

// Funktion zum Bearbeiten des "Im Newsletter"-Status
function toggleNewsletterStatus(productId, isChecked) {
    fetch('database.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'toggleNewsletterStatus',
            productId,
            isChecked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadProducts(); // Produkte neu laden
            loadNewsletter(); // Newsletter-Tabelle aktualisieren
        } else {
            alert('Status konnte nicht geändert werden');
        }
    });
}

// Funktion zum Löschen eines Produkts
function deleteProduct(productId) {
    fetch('database.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteProduct', productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadProducts(); // Produktliste neu laden
        } else {
            alert('Produkt konnte nicht gelöscht werden');
        }
    });
}

// Funktion zum Laden der Produkte, die im Newsletter sind
function loadNewsletter() {
    fetch('database.php?action=getNewsletterProducts')
        .then(response => response.json())
        .then(data => {
            const newsletterTableBody = document.querySelector('#newsletterTable tbody');
            newsletterTableBody.innerHTML = ''; // Newsletter-Tabelle zurücksetzen
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.categoryName}</td>
                    <td>${product.productName}</td>
                    <td>${product.productNumber}</td>
                    <td>${product.newsletterAddedAt}</td>
                `;
                newsletterTableBody.appendChild(row);
            });
        });
}

// Funktion zum Hinzufügen einer neuen Kategorie
function addCategory() {
    const newCategoryInput = document.getElementById('newCategoryInput').value;
    
    fetch('database.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'addCategory', categoryName: newCategoryInput })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCategories(); // Kategorien neu laden
        } else {
            alert('Kategorie konnte nicht hinzugefügt werden');
        }
    });
}

// Initialisieren der Funktionen beim Laden der Seite
document.addEventListener('DOMContentLoaded', function() {
    loadCategories(); // Kategoriedaten laden
    loadProducts(); // Produktdaten laden
    loadNewsletter(); // Newsletter-Produkte laden

    // Event Listener für "Im Newsletter"-Checkbox
    document.querySelector('#productTable').addEventListener('change', function(event) {
        if (event.target.classList.contains('newsletterCheckbox')) {
            const productId = event.target.getAttribute('data-id');
            const isChecked = event.target.checked;
            toggleNewsletterStatus(productId, isChecked);
        }
    });
});
    </script>
</body>
</html>
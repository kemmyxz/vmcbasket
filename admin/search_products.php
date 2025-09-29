<?php

require 'inc/config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT p.*, pv.id as variant_id,
        GROUP_CONCAT(
            CONCAT(pv.size, ' (', pv.gender, '): ', pv.stock, ' pcs') 
            ORDER BY pv.size, pv.gender
            SEPARATOR '<br>'
        ) as size_variants,
        SUM(pv.stock) as total_stock,
        CASE 
            WHEN p.type = 'Uniform' THEN 'Uniform'
            WHEN p.type = 'Supplies' THEN 'Supplies'
            ELSE 'Unknown'
        END as type_name
        FROM products p
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        WHERE p.product_name LIKE ? OR p.dr_number LIKE ?
        GROUP BY p.id
        ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td>
                <input type="checkbox" class="custom-checkbox product-checkbox" value="<?= $row['id']; ?>">
            </td>
            <td><?= $count++; ?></td>
            <td>
                <img src="<?= $row['image'] ?: 'default.png'; ?>" class="product-img" alt="Product Image">
            </td>
            <td>
                <strong><?= htmlspecialchars($row['product_name']); ?></strong><br>
                <small>D.R. No: <?= htmlspecialchars($row['dr_number']); ?></small>
            </td>
            <td>₱<?= number_format($row['price'], 2); ?></td>
            <td>
                <?php if (!empty($row['size_variants'])): ?>
                    <?= $row['size_variants']; ?><br>
                    <strong>Total: <?= $row['total_stock']; ?> pcs</strong>
                <?php else: ?>
                    <strong>Total: <?= $row['total_stock']; ?> pcs</strong>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['type_name'] === 'Uniform'): ?>
                    <span class="badge bg-primary"><?= htmlspecialchars($row['type_name']); ?></span>
                <?php else: ?>
                    <span class="badge bg-success"><?= htmlspecialchars($row['type_name']); ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?php
                // Get the latest restock history
                $restock_sql = "SELECT updated_by, restock_date 
                            FROM restock_history 
                            WHERE product_id = ? 
                            ORDER BY restock_date DESC 
                            LIMIT 1";
                $restock_stmt = $conn->prepare($restock_sql);
                $restock_stmt->bind_param("i", $row['id']);
                $restock_stmt->execute();
                $restock_result = $restock_stmt->get_result();
                
                if ($restock = $restock_result->fetch_assoc()) {
                    echo "Restocked by: " . htmlspecialchars($restock['updated_by']) . "<br>";
                    echo "<small class='text-muted'>" . date('M d, Y', strtotime($restock['restock_date'])) . "</small>";
                } else {
                    echo "<span class='text-muted'>No restock history</span>";
                }
                ?>
            </td>
            <td>
                <?php if ($row['total_stock'] > 0): ?>
                    <span class="status active">In Stock</span>
                <?php else: ?>
                    <span class="status inactive">Out of Stock</span>
                <?php endif; ?>
            </td>
            <!-- Add your action buttons here -->
            <td>
                <!-- Your existing action buttons code -->
            </td>
        </tr>
        <?php
    }
} else {
    echo '<tr><td colspan="10" class="text-center">No products found.</td></tr>';
}

$conn->close();
?>
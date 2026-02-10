<?php
include 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$wallet_res = mysqli_query($conn, "SELECT balance FROM wallets WHERE user_id='$user_id'");
$wallet = mysqli_fetch_assoc($wallet_res);

$trans_sql = "SELECT * FROM transactions WHERE from_user='$user_id' OR to_user='$user_id' ORDER BY created_at DESC";
$trans_res = mysqli_query($conn, $trans_sql);
?>

<div class="container">
    <h2 class="mt-3">My Wallet</h2>
    <div class="card text-center" style="background: #e3f2fd; padding: 40px;">
        <h3>Current Balance</h3>
        <h1 style="color: var(--primary-color);">LKR <?php echo number_format($wallet['balance'], 2); ?></h1>
        <?php if ($_SESSION['role'] == 'client'): ?>
            <button class="btn btn-primary" onclick="alert('Simulation: Funds Added!');">Add Funds (Simulated)</button>
        <?php endif; ?>
    </div>

    <div class="card mt-3">
        <h3>Transaction History</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd; text-align: left;">
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($t = mysqli_fetch_assoc($trans_res)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td><?php echo $t['created_at']; ?></td>
                        <td><?php echo htmlspecialchars($t['description']); ?></td>
                        <td>LKR <?php echo number_format($t['amount'], 2); ?></td>
                        <td>
                            <?php if ($t['to_user'] == $user_id): ?>
                                <span style="color: green; font-weight: bold;">Credit (+)</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Debit (-)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($trans_res) == 0): ?>
                    <tr><td colspan="4" class="text-center">No transactions yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

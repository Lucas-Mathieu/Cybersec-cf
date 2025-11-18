<?php require_once '../app/views/partials/header.php'; ?>

<div class="posts-container">
    <h2 class="posts-title">Gestion des Utilisateurs</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="success-msg" style="color: green; text-align: center;"><?= $_SESSION['success'] ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <thead>
            <tr style="background: #343a40; color: #fff;">
                <th style="padding: 15px; text-align: left;">ID</th>
                <th style="padding: 15px; text-align: left;">Nom</th>
                <th style="padding: 15px; text-align: left;">Email</th>
                <th style="padding: 15px; text-align: left;">Vérifié</th>
                <th style="padding: 15px; text-align: left;">Admin</th>
                <th style="padding: 15px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;"><?= htmlspecialchars($user['id']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($user['name']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 15px;"><?= $user['is_verified'] ? 'Oui' : 'Non' ?></td>
                    <td style="padding: 15px;"><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
                    <td style="padding: 15px;">
                        <form action="/admin/verify/<?= $user['id'] ?>" method="POST" style="display: inline;">
                            <button type="submit" class="btn <?= $user['is_verified'] ? 'btn-red-outline' : 'btn-primary' ?>">
                                <?= $user['is_verified'] ? 'Dé-vérifier' : 'Vérifier' ?>
                            </button>
                        </form>
                        <form action="/admin/toggle-admin/<?= $user['id'] ?>" method="POST" style="display: inline;">
                            <button type="submit" class="btn <?= $user['is_admin'] ? 'btn-red-outline' : 'btn-primary' ?>">
                                <?= $user['is_admin'] ? 'Retirer Admin' : 'Faire Admin' ?>
                            </button>
                        </form>
                        <form action="/admin/delete-user/<?= $user['id'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            <button type="submit" class="btn btn-red">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/partials/footer.php'; ?>
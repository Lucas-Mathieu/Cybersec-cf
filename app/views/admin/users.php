<?php require_once '../app/views/partials/header.php'; ?>

<div class="posts-container">
    <h2 class="posts-title">Gestion des Utilisateurs</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="success-msg"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <table class="admin-users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Vérifié</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['is_verified'] ? 'Oui' : 'Non' ?></td>
                    <td><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <form action="/admin/verify/<?= $user['id'] ?>" method="POST" class="inline-form">
                            <button type="submit" class="btn <?= $user['is_verified'] ? 'btn-red-outline' : 'btn-primary' ?>">
                                <?= $user['is_verified'] ? 'Dé-vérifier' : 'Vérifier' ?>
                            </button>
                        </form>
                        <form action="/admin/toggle-admin/<?= $user['id'] ?>" method="POST" class="inline-form">
                            <button type="submit" class="btn <?= $user['is_admin'] ? 'btn-red-outline' : 'btn-primary' ?>">
                                <?= $user['is_admin'] ? 'Retirer Admin' : 'Faire Admin' ?>
                            </button>
                        </form>
                        <form action="/admin/delete-user/<?= $user['id'] ?>" method="POST" class="inline-form" data-confirm="<?= htmlspecialchars('Êtes-vous sûr de vouloir supprimer cet utilisateur ?', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-red">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/partials/footer.php'; ?>

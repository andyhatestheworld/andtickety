<?php
require_once __DIR__ . '/../include/configuration/config.php';
$contacts = json_decode(file_get_contents('include/contacts.json'), true);
?>

<div class="contact-cards">
  <?php foreach ($contacts as $contact): ?>

    <div class="contact-card">

        <div>
            <h4><?= htmlspecialchars($contact['title']) ?></h4>
            <p>
                <?php if (!empty($contact['link'])): ?>
                    <a href="<?= htmlspecialchars($contact['link']) ?>" target="_blank" style="text-decoration: none; color: inherit;">
                        <?= htmlspecialchars($contact['value']) ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($contact['value']) ?>
                <?php endif; ?>

            </p>
        </div>
           
        <div class="svg-placeholder">
            <img src="https://cdn.simpleicons.org/<?= htmlspecialchars($contact['icon']) ?>" alt="<?= htmlspecialchars($contact['title']) ?>" />
        </div>

    </div>

  <?php endforeach; ?>
</div>

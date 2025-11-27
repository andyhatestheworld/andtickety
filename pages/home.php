<?php
require_once __DIR__ . '/../include/configuration/config.php';

$json_file = __DIR__ . '/../include/event_info.json';
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, true);
    $product = $data[0] ?? null;
}
if (!$product) {
    echo "Eroare: Nu s-au putut încărca datele evenimentului.";
    exit;
}

$dot_colors = ['🔵', '🔴', '🟠', '🟣', '🟢', '🟡', '🟤', '⚫', '⚪'];
?>

        <div class="product-container">

            <div class="product-image">

                <?php if (!empty($product['images'])): ?>
                    
                    <a href="<?= $product['images'][0] ?>" class="glightbox" data-gallery="product-<?= $product['id'] ?>">
                        <img src="<?= $product['images'][0] ?>" alt="<?= htmlspecialchars($product['event_name']) ?>" style="max-width: 100%; height: auto;">
                    </a>

                    <?php foreach (array_slice($product['images'], 1) as $img): ?>
                        <a href="<?= $img ?>" class="glightbox" data-gallery="product-<?= $product['id'] ?>" style="display: none;"></a>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <div class="product-info">
                <h1><?= htmlspecialchars($product['event_name']) ?></h1>

                <form action="<?php echo $site_url . '/actions/create_checkout.php'; ?>" method="POST">
                    
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($product['id']) ?>">
                    <input type="hidden" name="event_name" value="<?= htmlspecialchars($product['event_name']) ?>">

                    <table border="0" cellpadding="10" cellspacing="0" width=100%>
                        <thead>
                            <tr>
                                <th align="left" colspan="2"><strong>Buy tickets</strong></th>
                                <th align="right"><strong>Number of tickets</strong></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php 
                            $index = 0; 
                            $limit_per_category = $product['max_tickets_per_category'];
                            foreach ($product['tickets'] as $key => $ticket): 
                                $dot = $dot_colors[$index] ?? '⚪';
                                $current_limit = $limit_per_category;
                            ?>

                            <tr>
                                <td width="20"><?= $dot ?></td>
                                <td><?= htmlspecialchars($ticket['name']) ?></td>
                                <td align="right">
                                    <?= $ticket['price'] ?> <?= $currency_symbol ?> &nbsp;
                                    
                                    <button type="button" onclick="decrement('qty_<?= $key ?>')">-</button>

                                    <input type="text" id="qty_<?= $key ?>" 
                                        name="tickets[<?= $key ?>][qty]" 
                                        value="0" size="1" style="text-align: center;" data-max="<?= $current_limit ?>" readonly>

                                    <button type="button" onclick="increment('qty_<?= $key ?>')">+</button>

                                    <input type="hidden" name="tickets[<?= $key ?>][name]" value="<?= htmlspecialchars($ticket['name']) ?>">
                                    <input type="hidden" name="tickets[<?= $key ?>][price]" value="<?= $ticket['price'] ?>">
                                </td>
                            </tr>
                            <?php 
                                $index++; 
                            endforeach; 
                            ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="2"><h3>Total to pay</h3></td>
                                <td align="right">
                                    <strong id="total-price-display">0 <?= $currency_symbol ?></strong>
                                    <br>
                                    <strong id="total-tickets-display">(0 tickets)</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <button id="buy-btn" class="buy-button" type="submit" name="submit_order" disabled>BUY NOW</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                </form> 

            </div>

        </div>

<script>
function increment(id) {
    var el = document.getElementById(id);
    var currentVal = parseInt(el.value);
    
    var maxVal = parseInt(el.getAttribute('data-max'));

    if (currentVal < maxVal) {
        el.value = currentVal + 1;
        calcTotal();
    }
}

function decrement(id) {
    var el = document.getElementById(id);
    if(parseInt(el.value) > 0) {
        el.value = parseInt(el.value) - 1;
        calcTotal();
    }
}

function calcTotal() {
    let total = 0;
    let bilete = 0;
    let currency_symbol ="<?= $currency_symbol; ?>";
    
    const inputs = document.querySelectorAll('input[name*="[qty]"]');
    
    inputs.forEach(input => {
        let qty = parseInt(input.value);
        if(qty > 0) {
            let nameAttr = input.getAttribute('name');
            let priceName = nameAttr.replace('[qty]', '[price]');
            let priceInput = document.querySelector('input[name="' + priceName + '"]');
            let price = parseFloat(priceInput.value);
            
            total += qty * price;
            bilete += qty;
        }
    });

    document.getElementById('total-price-display').innerText = total + " " + currency_symbol;
    document.getElementById('total-tickets-display').innerText = "(" + bilete + " tickets)";

    var buyBtn = document.getElementById('buy-btn'); 
    
    if (buyBtn) {
        if (bilete > 0) {
            buyBtn.disabled = false;
            buyBtn.style.opacity = "1";
            buyBtn.style.cursor = "pointer";
        } else {
            buyBtn.disabled = true;
            buyBtn.style.opacity = "0.5";
            buyBtn.style.cursor = "not-allowed";
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calcTotal();
});
</script>
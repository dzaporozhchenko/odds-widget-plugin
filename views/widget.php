<div>
    <h3><?=$header?></h3>
    <strong><?=explode('T', $game['commence_time'])[0]?>: <?=$game['home_team']?> - <?=$game['away_team']?></strong>
    <table class="odds-table">
        <thead>
            <tr>
                <td>Bookmaker</td>
                <?php foreach ($outcomes as $outcome) { ?>
                    <td><?=$outcome?></td>
                <?php } ?>
            </tr>
        </thead>
        <tbody><?php
            foreach ($game['bookmakers'] as $bookmaker) {
                if (in_array($bookmaker['key'], array_keys($bookmakers))) { ?>
                    <tr>
                        <td><a href="<?=$bookmakers[$bookmaker['key']]?>"><?= $bookmaker['title'] ?></a></td>
                        <?php foreach ($bookmaker['markets'] as $market) {
                            if ($market['key'] != 'h2h') {
                                continue;
                            }
                            foreach ($outcomes as $outcome) {
                                foreach ($market['outcomes'] as $market_outcome) {
                                    if ($outcome === $market_outcome['name']) { ?>
                                        <td class="price-cell"><?=$market_outcome['price']?></td>
                                        <?php break;
                                    }
                                }
                            }
                        } ?>
                    </tr>
                <?php }
            }
        ?></tbody>
    </table>
</div>
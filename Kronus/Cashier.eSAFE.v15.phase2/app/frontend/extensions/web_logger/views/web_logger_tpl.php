<div>
    <h2>QUERIES</h2>
    <table>
        <thead>
            <tr>
                <th>STATEMENT</th><th>PARAMETER</th><th>EXECUTION TIME</th>
            </tr>
        </thead>
        <tbody>
            <?php $cntr=0; ?>
            <?php foreach($queries as $query): ?>
            <?php $style='style="background-color:#ccc"'; ?>
            <?php if($cntr % 2 == 0): ?>
            <?php $style='style="background-color:#ddd"'; ?>
            <?php endif; ?>
            <tr <?php echo $style; ?>>
                <td style="color: #DD0000"><?php echo $query['statement'] ?></td>
                <td style="color: #DD0000">
                    <?php $param = ''; ?>
                <?php if($query['parameter']): ?>    
                <?php foreach($query['parameter'] as $key => $val): ?>
                    <?php $param.=' '. $key.'=>'.$val; ?>
                <?php endforeach; ?>
                <?php endif; ?>    
                    <?php echo $param; ?>
                </td>
                <td style="color: #DD0000"><?php echo $query['time_executed'] ?></td>
            </tr>
            <?php $cntr++; ?>
            <?php endforeach; ?> 
        </tbody>

    </table>
</div>

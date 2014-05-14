<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Stacker Batch ID</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <table>
                <tr>
                    <td style = "width: 20%;"><?php echo CHtml::label('TerminalName','TerminalName'); ?></td>
                    <td><?php echo CHtml::textField('TerminalName'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 20%;"><?php echo CHtml::label('MembershipCardNumber','MembershipCardNumber'); ?></td>
                    <td><?php echo CHtml::textField('MembershipCardNumber'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 20%;"></td>
                    <td><?php echo CHtml::submitButton('Invoke'); ?></td>
                </tr>
            </table>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>

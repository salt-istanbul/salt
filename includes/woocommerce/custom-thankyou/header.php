<div class="content-centered">
    <div class="content-block">
        <div class="content-block-success w-100">
            <i class="fa fa-check"></i>
            <h3 class="title"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', _e( 'Your order received.', 'zitango' ), $order ); ?></h3>
            <div class="description">
                <?php echo sprintf(__('Order No: %s','jewelicon') , '<strong>'.$order->get_order_number().'</strong>'); ?>
                <br>
                <?php _e('Thank you for choosing us.','zitango'); ?>
            </div>
            <?php echo sprintf(__('We sent a confirmation mail to %s.','zitango') , '<strong>'.$order->get_billing_email().'</strong>'); ?>
            <br>
            <?php echo sprintf(__('You can contact us from  %s if you have a questions about your order.','zitango'), '<a href="mailto:info@zitango.com">info@zitango.com</a>' ); ?>

            <table class="table table-module table-border table-sm mt-5">
                <thead>
                    <th><?php _e( 'Order', 'woocommerce' ); ?></th>
                    <th><?php _e( 'Date', 'woocommerce' ); ?></th>
                    <th><?php _e( 'Total', 'woocommerce' ); ?></th>
                    <?php if ( $order->get_payment_method_title() ) : ?>
                    <th><?php _e( 'Payment method', 'woocommerce' ); ?></th>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $order->get_order_number(); ?></td>
                        <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_paid() ) ); ?></td>
                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                        <?php if ( $order->get_payment_method_title() ) : ?>
                            <td><?php echo $order->get_payment_method_title(); ?></td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
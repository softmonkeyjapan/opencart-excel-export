<?php echo $header; ?>
<div id="content">

    <!-- Start Breadcrumb -->
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) : ?>
            <?php echo $breadcrumb['separator']; ?>
            <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php endforeach; ?>
    </div>
    <!-- End Breadcrumb -->



    <!-- Start Box Table -->
    <div class="box">


        <!-- Start Heading box -->
        <div class="heading">
            <h1><img src="view/image/report.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="location = '<?php echo $button_export; ?>'" class="button"><?php echo $text_export_all; ?></a>
            </div>
        </div>
        <!-- End Heading box -->
    

        <!-- Start content -->
        <div class="content">
            <!-- Start Filter fields -->
            <table class="form">
                <tr>
                    <td>
                        <?php echo $entry_date_start; ?>
                        <input type="text" name="filter_date_start" value="<?php echo $filter_date_start; ?>" id="date-start" size="12" />
                    </td>
                    <td>
                        <?php echo $entry_date_end; ?>
                        <input type="text" name="filter_date_end" value="<?php echo $filter_date_end; ?>" id="date-end" size="12" />
                    </td>
                    <td>
                        <?php echo $entry_status; ?>
                        <select name="filter_order_status_id">
                            <option value="0"><?php echo $text_all_status; ?></option>
                            <?php foreach ($order_statuses as $order_status) : ?>
                                <?php if ($order_status['order_status_id'] == $filter_order_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>    
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td style="text-align: right;">
                        <a onclick="filter();" class="button"><?php echo $button_filter; ?></a>
                    </td>
                </tr>
            </table>
            <!-- End Filter fields -->
            

            <!-- Start list table -->
            <table class="list">
                <thead>
                    <tr>
                        <td class="left"><?php echo $text_customer; ?></td>
                        <td class="left"><?php echo $text_product; ?></td>
                        <td class="right"><?php echo $text_order; ?></td>
                        <td class="right"><?php echo $text_date; ?></td>
                        <td class="right"><?php echo $text_amount; ?></td>
                        <td class="right"><?php echo $text_status; ?></td>
                        <td class="right"><?php echo $text_action; ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders) { ?>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td class="left"><?php echo $order['firstname'] . ' ' . $order['lastname']; ?></td>
                                <td class="left"><?php echo $order['nb_product']; ?></td>
                                <td class="right"><?php echo $order['order_id']; ?></td>
                                <td class="right"><?php echo $order['date_added']; ?></td>
                                <td class="right"><?php echo $order['total']; ?></td>
                                <td class="right"><?php echo $order['status']; ?></td>
                                <td class="right">
                                    <?php foreach ($order['action'] as $action) : ?>
                                        [ 
                                            <a href="<?php echo $action['href']; ?>">
                                                <?php echo $action['text']; ?>
                                            </a> 
                                        ]
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php } else { ?>
                        <tr>
                            <td class="center" colspan="6"><?php echo 'No result'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!-- End list table -->


            <!-- Start pagination -->
            <div class="pagination">
                <?php //echo $pagination; ?>
            </div>
            <!-- End pagination -->
        </div>
        <!-- End content -->
    </div>
    <!-- End Box Table -->
</div>



<script type="text/javascript"><!--
    function filter() {
        var url                 = 'index.php?route=report/export_xls&token=<?php echo $token; ?>';
        var filter_date_start   = $('input[name=\'filter_date_start\']').attr('value');
        var filter_date_end     = $('input[name=\'filter_date_end\']').attr('value');
        var filter_order_status_id = $('select[name=\'filter_order_status_id\']').attr('value');  
  
        if (filter_date_start) {
            url += '&filter_date_start=' + encodeURIComponent(filter_date_start);
        }
        if (filter_date_end) {
            url += '&filter_date_end=' + encodeURIComponent(filter_date_end);
        }
        if (filter_order_status_id != 0) {
            url += '&filter_order_status_id=' + encodeURIComponent(filter_order_status_id);
        } 

        location = url;
    }
//--></script>

<script type="text/javascript"><!--
    $(document).ready(function() {
        $('#date-start').datepicker({dateFormat: 'yy-mm-dd'});  
        $('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script> 

<?php echo $footer; ?>
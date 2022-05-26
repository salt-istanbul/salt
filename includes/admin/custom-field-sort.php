<?php
/*
add_filter( 'parse_query', 'ba_admin_posts_filter' );
add_action( 'restrict_manage_posts', 'ba_admin_posts_filter_restrict_manage_posts' );

function ba_admin_posts_filter( $query )
{
    global $pagenow;
    if ( is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_NAME']) && $_GET['ADMIN_FILTER_FIELD_NAME'] != '') {
        $query->query_vars['meta_key'] = $_GET['ADMIN_FILTER_FIELD_NAME'];
    if (isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '')
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
    }
}

function ba_admin_posts_filter_restrict_manage_posts()
{
    global $wpdb;
    $sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta.' ORDER BY 1';
    $fields = $wpdb->get_results($sql, ARRAY_N);
?>
<select name="ADMIN_FILTER_FIELD_NAME">
<option value=""><?php _e('Filter By Custom Fields', 'baapf'); ?></option>
<?php
    $current = isset($_GET['ADMIN_FILTER_FIELD_NAME'])? $_GET['ADMIN_FILTER_FIELD_NAME']:'';
    $current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
    foreach ($fields as $field) {
        if (substr($field[0],0,1) != "_"){
        printf
            (
                '<option value="%s"%s>%s</option>',
                $field[0],
                $field[0] == $current? ' selected="selected"':'',
                $field[0]
            );
        }
    }
?>
</select> <?php _e('Value:', 'baapf'); ?><input type="TEXT" name="ADMIN_FILTER_FIELD_VALUE" value="<?php echo $current_v; ?>" />
<?php
}*/

/**
 * Add extra dropdowns to the List Tables
 *
 * @param required string $post_type    The Post Type that is being displayed
 */
//add_action('restrict_manage_posts', 'add_extra_tablenav');
function add_extra_tablenav($post_type){

    global $wpdb;
    $post_type_desired =" tour-plan";
    $field_name = "group-type";

    /** Ensure this is the correct Post Type*/
    if($post_type !== $post_type_desired)
        return;

    /** Grab the results from the DB */
    $query = $wpdb->prepare('
        SELECT DISTINCT pm.meta_value FROM %1$s pm
        LEFT JOIN %2$s p ON p.ID = pm.post_id
        WHERE pm.meta_key = "%3$s" 
        AND p.post_status = "%4$s" 
        AND p.post_type = "%5$s"
        ORDER BY "%3$s"',
        $wpdb->postmeta,
        $wpdb->posts,
        $field_name, // Your meta key - change as required
        'publish',          // Post status - change as required
        $post_type
    );
    $results = $wpdb->get_col($query);

    /** Ensure there are options to show */
    if(empty($results))
        return;

    // get selected option if there is one selected
    if (isset( $_GET[$field_name] ) && $_GET[$field_name] != '') {
        $selectedName = $_GET[$field_name];
    } else {
        $selectedName = -1;
    }

    /** Grab all of the options that should be shown */
    $options[] = sprintf('<option value="-1">%1$s</option>', __('All Types', 'zitango'));
    foreach($results as $result) :
        if ($result == $selectedName) {
            $options[] = sprintf('<option value="%1$s" selected>%2$s</option>', esc_attr($result), $result);
        } else {
            $options[] = sprintf('<option value="%1$s">%2$s</option>', esc_attr($result), $result);
        }
    endforeach;

    /** Output the dropdown menu */
    echo '<select class="" id="$field_name" name="$field_name">';
    echo join("\n", $options);
    echo '</select>';
}






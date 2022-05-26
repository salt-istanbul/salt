<?php

function kia_user_taxonomy_args( $args, $query_id, $atts ){
    // taxonomy params, this is not supported by WP_User_Query, but we're faking it with pre_user_query
    if ( $atts['taxonomy'] && $atts['terms'] ) {
        $terms = explode("|", $atts['terms'] );
        
        $args['tax_query'] = array(
            array(
                'taxonomy' => $atts['taxonomy'],
                'field'    => 'slug',
                'terms'    => $terms,
            ),
        );
    }
    //print_r($args);

    return $args;
}
add_filter( 'sul_user_query_args', 'kia_user_taxonomy_args', 10, 3 );           


/**
 * Fake a "tax_query"
 * @param obj $query - by reference
 * @param str $query_id
 */
function kia_user_taxonomy_query( $query ) { 
   global $wpdb;

    // fake a tax query
    if ( isset( $query->query_vars['tax_query'] ) && is_array( $query->query_vars['tax_query'] ) ) {

        $sql = get_tax_sql( $query->query_vars['tax_query'], $wpdb->prefix . 'users', 'ID' );
        
        if( isset( $sql['join'] ) ){
            $query->query_from .= $sql['join'];
        }
        
        if( isset( $sql['where'] ) ){
            $query->query_where .= $sql['where'];
        }
        
    }
}
add_action( 'pre_user_query', 'kia_user_taxonomy_query' );

function get_terms_for_user( $user = false, $taxonomy = '' ) {

    // Verify user ID
    $user_id = is_object( $user )
        ? $user->ID
        : absint( $user );

    // Bail if empty
    if ( empty( $user_id ) ) {
        return false;
    }

    // Return user terms
    return wp_get_object_terms( $user_id, $taxonomy, array(
        'fields' => 'all_with_object_id'
    ) );
}

function get_term_slugs_to_ids($slugs=array(), $taxonomy=""){
    global $wpdb;
    $results = array();
    if($slugs && !empty($taxonomy)){
        if(!is_array($slugs)){
           $slugs = [$slugs];
        }
        $slug_where = " and (";
        foreach($slugs as $key=>$slug){
            $slug_where .= "t.slug = '$slug'";
            if($key < count($slugs)-1){
               $slug_where .= " or ";
            }
        }
        $slug_where .= ")";
        $query = "SELECT DISTINCT t.term_id as id FROM wp_term_taxonomy tt
                    INNER JOIN wp_terms AS t ON (t.term_id = tt.term_id)
                    WHERE tt.taxonomy = '$taxonomy' $slug_where";
        $results = $wpdb->get_results($query);
        if($results){
           $results = wp_list_pluck($results, "id");
        }
    }
    return $results;
}



class User extends TimberUser {
   
}

class Project extends TimberPost {
    public function get_term_slugs(){
        $slugs = "";
        $terms = $this->get_terms("project-categories");
        if($terms){
            $terms = wp_list_pluck($terms, "slug");
            $slugs = implode(" ", $terms);           
        }
        return $slugs;
    }
    
}
/*
class User extends Timber\User {
    public function get_terms($taxonomy="") {
        return get_terms_for_user( $this, $taxonomy);
    }
}

class Packages extends TimberTerm {
    public function get_lowest_price() {
        return get_pack_lowest_price( $this->term_id);
    }
}

add_filter( 'timber/user/classmap', function( $classmap ) {
    $custom_classmap = [
        'user' => User::class,
    ];
    return array_merge( $classmap, $custom_classmap );
} );
*/
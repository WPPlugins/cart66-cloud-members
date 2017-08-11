<?php

class CM_Admin_Restriction_Options extends CC_Admin_Settings_Field {

    public function render( $args ) {
        $list = $this->category_tree($args);
        if ( ! empty( $this->description ) ) {
            echo '<p>' . $this->description . '</p>';
        }
        echo $list;
    }
    

    public function category_tree( $args, $parent='0', &$level=0 ) {
        $out = '';

        $category_args = array(
        	'type'         => 'post',
        	'child_of'     => 0,
        	'parent'       => $parent,
        	'orderby'      => 'name',
        	'order'        => 'ASC',
        	'hide_empty'   => 0,
        	'hierarchical' => 1,
        	'taxonomy'     => 'category'
        );

        $categories = get_categories( $category_args );
        
        if( is_array( $categories ) ) {
            
            $products = CM_Cloud_Expiring_Products::instance();
            $memberships = $products->expiring_product_list();

            foreach( $categories as $cat ) {
                $indent = str_repeat( '&mdash;&nbsp;', $level );
                $out .= '<h4>' . $indent . $cat->name . '</h4>';

                foreach($memberships as $name => $id) {
                    $checked = '';

                    if( isset( $this->value[ $cat->term_id ] ) 
                        && is_array( $this->value[ $cat->term_id ] ) 
                        && in_array( $id, $this->value[ $cat->term_id ] ) ) {
                        $checked = 'checked="checked"';
                    }

                    $out .= '<input type="checkbox" name="cart66_members_restrictions[' . $cat->term_id . '][]    " value="' . $id . '" ' . $checked . '> ' . $name . '<br/>';
                }

                $depth = $level+1;
                $out .= $this->category_tree($args, $cat->term_id, $depth);
            }
        }
        else {
            echo "Categories is not an array. Parent: $parent :: Level: $level<br/>\n";
        }
        
        return $out;
    }    
}

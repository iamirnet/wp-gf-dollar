<?php

if ( ! class_exists( 'GFForms' ) || ! class_exists( 'GF_Field' ) ) {
    die();
}


class iAmirGFDollarField extends GF_Field
{
    public $type = 'calc_dollar';
    //public $inputType = 'prodct';


    // The rest of the code is added here...
    function get_form_editor_field_settings()
    {
        return array(
            'conditional_logic_field_setting',
            'rules_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'placeholder_setting',
            'size_setting',
        );
    }

    public function is_conditional_logic_supported() {
        return true;
    }


    public function get_form_editor_field_title()
    {
        return esc_attr__('دلار', 'i-amir-gf-dollar');
    }

    public function get_form_inline_script_on_page_render( $form ) {
        $id       = (int) $this->id;
        $form_id         = absint( $form['id'] );
        $script = "document.getElementById('input_{$form_id}_$id').addEventListener('blur', function(e) { 
                var value = parseFloat(e.target.value);
                jQuery.ajax({
                  method: 'POST',
                  url: '/wp-json/iamir/api/v1/dollar',
                  data: { value: value }
                }).done(function (response) {   // success callback function
                        if(response.data && response.data.status) {
                            var price = response.data.amount;
                            gform.addFilter( 'gform_product_total', function(total, formId){
                                    return price;
                                 });
                            gformCalculateTotalPrice(formId);
                        }
                    });

             });";

        return $script;
    }


    public function validate( $value, $form ) {
        $price = GFCommon::to_number( $value );
        if ( ! rgblank( $value ) && ( $price === false || $price < 0 ) ) {
            $this->failed_validation  = true;
            $this->validation_message = empty( $this->errorMessage ) ? __( 'Please enter a valid value.', 'gravityforms' ) : $this->errorMessage;
        }
    }

    public function get_field_input( $form, $value = '', $entry = null ) {
        $form_id         = absint( $form['id'] );
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $id       = (int) $this->id;
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

        $value = esc_attr( $value );

        $placeholder_attribute = $this->get_field_placeholder_attribute();

        $size         = $this->size;
        $class_suffix = $is_entry_detail ? '_admin' : '';
        $class        = $size . $class_suffix;
        $class        = esc_attr( $class );

        $disabled_text      = $is_form_editor ? 'disabled="disabled"' : '';
        $required_attribute = $this->isRequired ? 'aria-required="true"' : '';
        $invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

        $tabindex = $this->get_tabindex();

        return "<div class='ginput_container ginput_container_product_price'>
					<input name='input_{$id}' id='{$field_id}' type='text' value='{$value}' class='{$class} ' {$tabindex} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text}/>
				</div>";


    }
}

GF_Fields::register(new iAmirGFDollarField());
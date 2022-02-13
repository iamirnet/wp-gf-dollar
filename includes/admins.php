<?php


function i_amir_gf_dollar_menu() {
    add_menu_page("تنظیمات فیلد دلار", "فیلد دلار","manage_options", "i_amir_gf_dollar", "i_amir_gf_dollar_setting");
}


add_action("admin_menu", "i_amir_gf_dollar_menu");

function i_amir_gf_dollar_setting(){

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if ($_POST){
        foreach (['fee_list', 'currency_unit', 'dollar_default'] as $item) {
            if (isset($_POST[$item]) && (is_array($_POST[$item]) ? count($_POST[$item]) : strlen($_POST[$item]))) {
                update_option("i_amir_gf_dollar_$item", $_POST[$item]);
            }
        }
    }
    $fee_list = get_option("i_amir_gf_dollar_fee_list", [["type"=>"percent","value"=>"","rule"=>"=","count"=>""]]);
    $currency_unit = get_option("i_amir_gf_dollar_currency_unit", 'IRT');
    $dollar_default = get_option("i_amir_gf_dollar_dollar_default");
    ?>
    <div class="wrap">

        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post">
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="currency_unit">واحد پول</label>
                <div class="col-lg-4">
                    <select name="fee_type" id="currency_unit" class="form-control">
                        <option value="IRT" <?php echo $currency_unit == "IRT" ? "selected" : null; ?>>تومان</option>
                        <option value="IRR" <?php echo $currency_unit == "IRR" ? "selected" : null; ?>>ریال</option>
                    </select>
                </div>
            </div>
            <div class="form-group row my-2">
                <label class="col-lg-2 col-form-label text-right" for="dollar_default">مبلغ پیش فرض دلار</label>
                <div class="col-lg-4">
                    <input name="dollar_default" type="text" id="dollar_default" class="form-control text-left" dir="ltr" value="<?php echo $dollar_default; ?>" placeholder="قیمت پیش فرض دلار را وارد کنید">
                    <span class="form-text text-muted">درصورتی که افزونه از وب سرویس قیمت را نتواند دریافت کند از این قیمت استفاده می شود.</span>
                </div>
            </div>
            <div id="kt_repeater_1" class="repeater my-2">
                <div class="form-group row" id="kt_repeater_1">
                    <label class="col-lg-2 col-form-label text-right">کارمزد ها:</label>
                    <div data-repeater-list="fee_list" class="col-lg-10">

                        <?php
                            foreach ($fee_list as $fee_item) {
                                ?>
                                    <div data-repeater-item class="form-group row align-items-center">
                                    <div class="col-md-2">
                                        <label>نوع:</label>
                                        <select class="form-control" name="type">
                                            <option value="percent" <?php echo $fee_item['type'] == "IRT" ? "selected" : null; ?>>درصد</option>
                                            <option value="currency" <?php echo $fee_item['type'] == "IRR" ? "selected" : null; ?>>ثابت</option>
                                        </select>
                                        <div class="d-md-none mb-2"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>کارمزد:</label>
                                        <input type="text" name="value" value="<?php echo $fee_item['value'] ?>" class="form-control ltr text-left" placeholder="مقدار کارمزد"/>
                                        <div class="d-md-none mb-2"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>شرط:</label>
                                        <select class="form-control" name="rule">
                                            <option value="=" <?php echo $fee_item['rule'] == "=" ? "selected" : null; ?>>هست</option>
                                            <option value="!=" <?php echo $fee_item['rule'] == "!=" ? "selected" : null; ?>>نیست</option>
                                            <option value=">" <?php echo $fee_item['rule'] == ">" ? "selected" : null; ?>>بیشتر یا بزرگتر از</option>
                                            <option value="<" <?php echo $fee_item['rule'] == "<" ? "selected" : null; ?>>کمتر یا کوچکتر از</option>
                                        </select>
                                        <div class="d-md-none mb-2"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>مقدار:</label>
                                        <input type="text" name="count" value="<?php echo $fee_item['count'] ?>"  class="form-control ltr text-left" placeholder="مقدار دلار"/>
                                        <div class="d-md-none mb-2"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-danger mt-4">
                                            <i class="la la-trash-o"></i>حذف
                                        </a>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>

                    </div>
                </div>
                <div class="form-group row mt-2">
                    <label class="col-lg-2 col-form-label text-right"></label>
                    <div class="col-lg-4">
                        <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-primary">
                            <i class="la la-plus"></i>افزودن
                        </a>
                    </div>
                </div>
            </div>

            <script>
                $('#kt_repeater_1').repeater({
                    show: function () {
                        $(this).slideDown();
                    },

                    hide: function (deleteElement) {
                        $(this).slideUp(deleteElement);
                    },

                    isFirstItemUndeletable: true
                });
            </script>
            <?php
            wp_nonce_field('acme-settings-save', 'acme-custom-message');
            submit_button();
            ?>
        </form>

    </div><!-- .wrap -->
    <?php
}

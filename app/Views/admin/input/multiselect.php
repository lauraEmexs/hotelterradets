<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php echo form_label($objects[$name]['label'], $name); ?><br/>
        <?php

        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }

        if($value != ''){
            $selected = $selected_tmp = explode(",", $value);
            $values = [];

            while ($id = array_shift($selected_tmp)) {
                if (isset($objects[$name]['content'][$id])) {
                    $values[$id] = $objects[$name]['content'][$id];
                }
                unset($objects[$name]['content'][$id]);
            }

            $values = $values + $objects[$name]['content'];
        } else {
            $values = $objects[$name]['content'];
            $selected = [];
        }

        $id = random_string('alpha', 16);

        $related_table = (isset($objects[$name]['related_table'])) ? 'data-related-table="'. $objects[$name]['related_table'] .'"' : '';
        echo form_multiselect(
            $name .'[]',
            $values,
            $selected,
            'class="autoselect2 select2" id="'. $id .'" style="width:100%;" '. $related_table
        );
        ?>
        <?php echo form_error($name);?>

        <script>
            function initialize_select2(selector) {
                if (selector === undefined) {
                    selector = '.assigned-blocks .autoselect2';
                }

                $(selector).select2({
                    placeholder: '',
                    width: '100%',
                    formatResult: function (item, container) {
                        if (item.element[0].dataset.img) {
                            return "<img width='100px' height='100px' src='" + item.element[0].dataset.img + "'/><span>" + item.text + "</span>";
                        } else
                            return item.text;
                    }
                });

                /* Make it sortable and keep selected order */
                $(selector).parent().find("ul.select2-selection__rendered").sortable({
                    containment: 'parent',
                    update: function() {
                        orderSortedValues($(this));
                    }
                });

                orderSortedValues = function(selector) {
                    $(selector).parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj){
                        var element = $(selector).parents('.form-group').find("option").filter(function () { return $(this).html() === obj.title; });

                        moveElementToEndOfParent(element)
                    });
                };

                moveElementToEndOfParent = function(element) {
                    var parent = element.parent();
                    element.detach();
                    parent.append(element);
                };

                /* Stop automatic ordering */
                $(selector).on("select2:select", function (evt) {
                    var id = evt.params.data.id;
                    var element = $(this).children("option[value="+id+"]");

                    moveElementToEndOfParent(element);

                    $(this).trigger("change");
                });
                /* End sortable */
            }
            initialize_select2("select#<?php echo $id; ?>:visible");
        </script>
    </div>
</div>

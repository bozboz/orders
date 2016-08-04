<?php

namespace Bozboz\Ecommerce\Orders\ListingFilters;

use Input, Form;
use Illuminate\Database\Eloquent\Builder;
use Bozboz\Admin\Reports\Filters\ListingFilter;

class DateFilter extends ListingFilter
{
    protected function defaultFilter($field)
    {
        return function($builder, $values) use ($field)
        {
            $fromDate = $values['from_date'];
            if ($fromDate) {
                $fromDate .= ' 00:00:00';
            }

            $toDate = $values['to_date'];
            if ($toDate) {
                $toDate .= ' 23:59:59';
            }

            if ($fromDate && $toDate) {
                $builder->whereBetween($this->name, [$fromDate, $toDate]);
            } elseif ($fromDate && ! $toDate) {
                $builder->where($this->name, '>=', $fromDate);
            } elseif (! $fromDate && $toDate) {
                $builder->where($this->name, '<=', $toDate);
            }
        };
    }

    public function filter(Builder $builder)
    {
        $this->call($builder, Input::only('from_date', 'to_date'));
    }

    public function __toString()
    {
        $fromDate = Input::get('from_date');
        $toDate = Input::get('to_date');

        $label = Form::label($this->name);

        return <<<HTML
            {$label}
            <div class="input-group input-group-sm">
                <input type="text"
                    name="from_date"
                    class="js-date-range-filter js-from-date-filter form-control"
                    data-date="{$fromDate}"
                    data-onchange-affect-input=".js-to-date-filter"
                    data-onchange-affect-boundary="minDate"
                >
                <span class="input-group-addon">
                    <label for="to_date" class="sr-only">To</label>
                    To
                </span>
                <input type="text"
                    name="to_date"
                    class="js-date-range-filter js-to-date-filter form-control"
                    data-date="{$toDate}"
                    data-onchange-affect-input=".js-from-date-filter"
                    data-onchange-affect-boundary="maxDate"
                >
                <div class="input-group-btn">
                    <button type="submit" value="Filter" class="btn btn-sm btn-default">Filter</button>
                </div>
            </div>

            <script>
                $(function() {
                    var prettyDateFormat = 'dd/mm/yy';
                    var isoDateFormat = 'yy-mm-dd';

                    $('.js-date-range-filter').each(function() {
                        var altInput = $(this);
                        var input = $('<input>', {
                            type: 'text',
                            class: altInput.attr('class'),
                            name: altInput.prop('name') + '_alt',
                        });

                        altInput.attr('class', '');

                        input.insertAfter(altInput);

                        altInput.prop('type', 'hidden');

                        input.datepicker({
                            dateFormat: prettyDateFormat,
                            setDate: altInput.data('date'),
                            altField: altInput,
                            altFormat: isoDateFormat,
                            numberOfMonths: 3,
                            onClose: function( selectedDate ) {
                                $(altInput.data('onchange-affect-input')).datepicker( "option", altInput.data('onchange-affect-boundary'), selectedDate );
                                if (selectedDate === '') {
                                    altInput.val('');
                                }
                            }
                        });

                        if (altInput.data('date')) {
                            input.datepicker('setDate', new Date(altInput.data('date')));
                        }
                    });
                });
              </script>
HTML;
    }
}

<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Fields;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

/**
 * Creating repeater fields based on the fields which provided to the endpoint.
 *
 * Class RepeaterField
 *
 * @method $this min($value = null)
 * @method $this max($value = null)
 * @method $this required($value = true)
 * @method $this help(string $value = null)
 * @method $this name($value = true)
 * @method $this button_label(string $value = null)
 * @method $this title(string $value = null)
 */
class Repeater extends Field
{
    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'platform::fields.repeater';

    /**
     * Required Attributes.
     *
     * @var array
     */
    protected $required = [
        'name',
        'layout',
    ];

    /**
     * Default attributes value.
     *
     * @var array
     */
    protected $attributes = [
        'class' => 'form-control',
        'original_name' => null,
        'template' => null,
        'button_label' => null,
        'ajax_data' => '[]',
    ];

    /**
     * Attributes available for a particular tag.
     *
     * @var array
     */
    protected $inlineAttributes = [
        'required',
        'min',
        'max',
        'name',
        'ajax_data',
    ];

    /**
     * @param  string  $layout
     * @return self
     */
    public function layout(string $layout): self
    {
        if (! class_exists($layout) && ! (app($layout) instanceof Rows)) {
            throw new \InvalidArgumentException(
                __('":class" does not exists or not supported. Only rows supported by repeater.', [
                    'class' => $layout,
                ]));
        }

        $this->set('layout', Crypt::encryptString($layout));

        $this->addBeforeRender(function () {
            $value = $this->get('value') ?? old($this->getOldName());

            if (! is_iterable($value)) {
                $value = Arr::wrap($value);
            }

            $this->set('value', $value);
        });

        return $this;
    }

    /**
     * Creating an instance of the repeater field.
     *
     * @param  string  $name
     * @return Repeater
     */
    public static function make(string $name = null): self
    {
        return (new static)->name($name)
            ->set('original_name', $name)
            ->value([])
            ->set('template', 'repeater_'.Str::random(32));
    }

    /**
     * @param  string  $view
     * @return Repeater
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the ajax_data passing to the component with each request.
     * If callback used it should return the array of the values passed to the ajax_data.
     *
     * @param callable|array $value
     * @return $this
     */
    public function ajaxData($value): self
    {
        if (is_callable($value)) {
            $value = call_user_func($value);
        }

        if (is_array($value)) {
            $this->set('ajax_data', json_encode($value, JSON_THROW_ON_ERROR));
        }

        return $this;
    }
}

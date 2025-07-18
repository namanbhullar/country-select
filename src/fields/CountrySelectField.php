<?php
/**
 * Country Select plugin for Craft CMS 5.x
 *
 * Country select field type.
 *
 * @link      https://github.com/EvolutedNewMedia/craft-countryselect
 * @copyright Copyright (c) 2025 Rack infosolution Pvt Ltd
 */

namespace namanbhullar\countryselect\fields;

use namanbhullar\countryselect\CountrySelect;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;

/**
 * @author    Rick Mills
 * @package   CountrySelect
 * @since     1.0.0
 */
class CountrySelectField extends CountrySelectBaseOptionsField
{
    // Properties
    // =========================================================================

    /**
     * @var array The available options
     */
    public array $options = [];

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('country-select', 'Country Select');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'country-select/_select',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'options' => $this->options,
            ]
        );
    }
}

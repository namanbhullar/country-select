<?php
/**
 * Country Select plugin for Craft CMS 5.x
 *
 * Country select field type.
 *
 * @link      https://github.com/EvolutedNewMedia/craft-countryselect
 * @copyright Copyright (c) 2025 Rack infosolution Pvt Ltd
 */

namespace evoluted\countryselect\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;

/**
 * @author    Rick Mills
 * @package   CountrySelect
 * @since     1.0.0
 */
class CountrySelectMultiField extends CountrySelectBaseOptionsField
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('country-select', 'Country Multi-select');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->multi = true;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ?ElementInterface $element = null): string
    {
        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'country-select/_multiselect',
            [
                'name' => $this->handle,
                'values' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'options' => $this->translatedOptions(),
            ]
        );
    }
}

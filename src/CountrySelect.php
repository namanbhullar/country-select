<?php
/**
 * Country Select plugin for Craft CMS 5.x
 *
 * Country select field type.
 *
 * @link      https://github.com/EvolutedNewMedia/craft-countryselect
 * @copyright Copyright (c) 2025 Rack infosolution Pvt Ltd
 */

namespace evoluted\countryselect;

use evoluted\countryselect\fields\CountrySelectField as CountrySelectField;
use evoluted\countryselect\fields\CountrySelectMultiField as CountrySelectMultiField;
use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Event;

/**
 * Class CountrySelect
 *
 * @author    Rick Mills
 * @package   CountrySelect
 * @since     1.0.0
 *
 */
class CountrySelect extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CountrySelect $plugin Country Select plugin instance
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string Plugin version
     */
    public string $schemaVersion = '2.0.0';

    /**
     * @var bool Whether the plugin has a settings view
     */
    public bool $hasCpSettings = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = CountrySelectField::class;
                $event->types[] = CountrySelectMultiField::class;
            }
        );

        Craft::info(
            Craft::t(
                'country-select',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }
}

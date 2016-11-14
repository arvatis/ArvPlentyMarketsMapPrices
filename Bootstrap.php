<?php

class Shopware_Plugins_Backend_ArvPlentyMarketsMapPrices_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var array
     */
    private static $customerGroupToPriceFieldMapping = [
        'H' => 'Price8',
        'HEU' => 'Price9'
    ];

    /**
     * Get (nice) name for plugin manager list
     *
     * @return string
     */
    public function getLabel()
    {
        return 'PlentyMarkets Price to Customer Class Mapping';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return array
     */
    public function getInfo() {
        return [
            'version' => $this->getVersion(),
            'autor' => 'arvatis media GmbH',
            'label' => $this->getLabel(),
            'source' => 'Community',
            'description' => '',
            'license' => 'MIT',
            'copyright' => 'Copyright © '. date('Y') . ', arvatis media GmbH',
            'support' => '',
            'link' => 'http://www.arvatis.com/'
        ];
    }

    /**
     * Standard plugin install method to register all required components.
     *
     * @throws \Exception
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('PlentyConnector_ImportEntityItemPrice_AfterGetPrice', 'onItemPriceUpdate');

        return true;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     *
     * @return array
     */
    public function onItemPriceUpdate(Enlight_Event_EventArgs $args)
    {
        /**
         * @var array $prices
         */
        $prices = $args->getReturn();

        /**
         * @var PlentySoapObject_ItemPriceSet $priceset
         */
        $priceset = $args->get('priceset');

        foreach (self::$customerGroupToPriceFieldMapping as $customerGroup => $priceField) {
            $price = [];

            $price['customerGroupKey'] = $customerGroup;
            $price['price'] = (!empty($priceset->{$priceField}) ? $priceset->{$priceField} : $priceset->Price);

            if (isset($priceset->PurchasePriceNet) && !is_null($priceset->PurchasePriceNet))
            {
                $price['basePrice'] = $priceset->PurchasePriceNet;
            }

            if (isset($priceset->RRP) && !is_null($priceset->RRP) && isset($price['price']) && ($priceset->RRP > $price['price']))
            {
                $price['pseudoPrice'] = $priceset->RRP;
            }

            $prices[] = $price;
        }

        return $prices;
    }
}

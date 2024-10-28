<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Field\EventHandlers\Fields\Tools;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Currency implements HandlerInterface
{
    private array $backTrace = [];

    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'Currency',
            'Add Currency',
            'Tool',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Currency';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Currency';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= <<<Frag
<div class="form-group margin-top:0">
    <select id="{$fieldName}_$changeID" name="$inputName" class="default-selector">
Frag;
        $currencies = [
            0 => ['symbol' => '$', 'name' => 'US Dollar', 'code' => 'USD', 'numeric_code' => '840', 'name_plural' => 'US dollars', 'symbol_native' => '$',],

            1 => ['symbol' => 'CA$', 'name' => 'Canadian Dollar', 'code' => 'CAD', 'numeric_code' => '124', 'name_plural' => 'Canadian dollars', 'symbol_native' => '$',],

            2 => ['symbol' => '€', 'name' => 'Euro', 'code' => 'EUR', 'numeric_code' => '978', 'name_plural' => 'euros', 'symbol_native' => '€',],

            3 => ['symbol' => '₦', 'name' => 'Nigerian Naira', 'code' => 'NGN', 'numeric_code' => '566', 'name_plural' => 'Nigerian nairas', 'symbol_native' => '₦',],

            4 => ['symbol' => 'Af', 'name' => 'Afghan Afghani', 'code' => 'AFN', 'numeric_code' => '971', 'name_plural' => 'Afghan Afghanis', 'symbol_native' => '؋',],

            5 => ['symbol' => 'ALL', 'name' => 'Albanian Lek', 'code' => 'ALL', 'numeric_code' => '8', 'name_plural' => 'Albanian lekë', 'symbol_native' => 'Lek',],

            6 => ['symbol' => 'AMD', 'name' => 'Armenian Dram', 'code' => 'AMD', 'numeric_code' => '51', 'name_plural' => 'Armenian drams', 'symbol_native' => 'դր.',],

            7 => ['symbol' => 'AR$', 'name' => 'Argentine Peso', 'code' => 'ARS', 'numeric_code' => '32', 'name_plural' => 'Argentine pesos', 'symbol_native' => '$',],

            8 => ['symbol' => 'AU$', 'name' => 'Australian Dollar', 'code' => 'AUD', 'numeric_code' => '36', 'name_plural' => 'Australian dollars', 'symbol_native' => '$',],

            9 => ['symbol' => 'man.', 'name' => 'Azerbaijani Manat', 'code' => 'AZN', 'numeric_code' => '944', 'name_plural' => 'Azerbaijani manats', 'symbol_native' => 'ман.',],

            10 => ['symbol' => 'KM', 'name' => 'Bosnia-Herzegovina Convertible Mark', 'code' => 'BAM', 'numeric_code' => '977', 'name_plural' => 'Bosnia-Herzegovina convertible marks', 'symbol_native' => 'KM',],

            11 => ['symbol' => 'Tk', 'name' => 'Bangladeshi Taka', 'code' => 'BDT', 'numeric_code' => '50', 'name_plural' => 'Bangladeshi takas', 'symbol_native' => '৳',],

            12 => ['symbol' => 'BGN', 'name' => 'Bulgarian Lev', 'code' => 'BGN', 'numeric_code' => '975', 'name_plural' => 'Bulgarian leva', 'symbol_native' => 'лв.',],

            13 => ['symbol' => 'BD', 'name' => 'Bahraini Dinar', 'code' => 'BHD', 'numeric_code' => '48', 'name_plural' => 'Bahraini dinars', 'symbol_native' => 'د.ب.‏',],

            14 => ['symbol' => 'FBu', 'name' => 'Burundian Franc', 'code' => 'BIF', 'numeric_code' => '108', 'name_plural' => 'Burundian francs', 'symbol_native' => 'FBu',],

            15 => ['symbol' => 'BN$', 'name' => 'Brunei Dollar', 'code' => 'BND', 'numeric_code' => '96', 'name_plural' => 'Brunei dollars', 'symbol_native' => '$',],

            16 => ['symbol' => 'Bs', 'name' => 'Bolivian Boliviano', 'code' => 'BOB', 'numeric_code' => '68', 'name_plural' => 'Bolivian bolivianos', 'symbol_native' => 'Bs',],

            17 => ['symbol' => 'R$', 'name' => 'Brazilian Real', 'code' => 'BRL', 'numeric_code' => '986', 'name_plural' => 'Brazilian reals', 'symbol_native' => 'R$',],

            18 => ['symbol' => 'BWP', 'name' => 'Botswanan Pula', 'code' => 'BWP', 'numeric_code' => '72', 'name_plural' => 'Botswanan pulas', 'symbol_native' => 'P',],

            19 => ['symbol' => 'Br', 'name' => 'Belarusian Ruble', 'code' => 'BYN', 'numeric_code' => '933', 'name_plural' => 'Belarusian rubles', 'symbol_native' => 'руб.',],

            20 => ['symbol' => 'BZ$', 'name' => 'Belize Dollar', 'code' => 'BZD', 'numeric_code' => '84', 'name_plural' => 'Belize dollars', 'symbol_native' => '$',],

            21 => ['symbol' => 'CDF', 'name' => 'Congolese Franc', 'code' => 'CDF', 'numeric_code' => '976', 'name_plural' => 'Congolese francs', 'symbol_native' => 'FrCD',],

            22 => ['symbol' => 'CHF', 'name' => 'Swiss Franc', 'code' => 'CHF', 'numeric_code' => '756', 'name_plural' => 'Swiss francs', 'symbol_native' => 'CHF',],

            23 => ['symbol' => 'CL$', 'name' => 'Chilean Peso', 'code' => 'CLP', 'numeric_code' => '152', 'name_plural' => 'Chilean pesos', 'symbol_native' => '$',],

            24 => ['symbol' => 'CN¥', 'name' => 'Chinese Yuan', 'code' => 'CNY', 'numeric_code' => '156', 'name_plural' => 'Chinese yuan', 'symbol_native' => 'CN¥',],

            25 => ['symbol' => 'CO$', 'name' => 'Colombian Peso', 'code' => 'COP', 'numeric_code' => '170', 'name_plural' => 'Colombian pesos', 'symbol_native' => '$',],

            26 => ['symbol' => '₡', 'name' => 'Costa Rican Colón', 'code' => 'CRC', 'numeric_code' => '188', 'name_plural' => 'Costa Rican colóns', 'symbol_native' => '₡',],

            27 => ['symbol' => 'CV$', 'name' => 'Cape Verdean Escudo', 'code' => 'CVE', 'numeric_code' => '132', 'name_plural' => 'Cape Verdean escudos', 'symbol_native' => 'CV$',],

            28 => ['symbol' => 'Kč', 'name' => 'Czech Republic Koruna', 'code' => 'CZK', 'numeric_code' => '203', 'name_plural' => 'Czech Republic korunas', 'symbol_native' => 'Kč',],

            29 => ['symbol' => 'Fdj', 'name' => 'Djiboutian Franc', 'code' => 'DJF', 'numeric_code' => '262', 'name_plural' => 'Djiboutian francs', 'symbol_native' => 'Fdj',],

            30 => ['symbol' => 'Dkr', 'name' => 'Danish Krone', 'code' => 'DKK', 'numeric_code' => '208', 'name_plural' => 'Danish kroner', 'symbol_native' => 'kr',],

            31 => ['symbol' => 'RD$', 'name' => 'Dominican Peso', 'code' => 'DOP', 'numeric_code' => '214', 'name_plural' => 'Dominican pesos', 'symbol_native' => 'RD$',],

            32 => ['symbol' => 'DA', 'name' => 'Algerian Dinar', 'code' => 'DZD', 'numeric_code' => '12', 'name_plural' => 'Algerian dinars', 'symbol_native' => 'د.ج.‏',],

            33 => ['symbol' => 'Ekr', 'name' => 'Estonian Kroon', 'code' => 'EEK', 'numeric_code' => '233', 'name_plural' => 'Estonian kroons', 'symbol_native' => 'kr',],

            34 => ['symbol' => 'EGP', 'name' => 'Egyptian Pound', 'code' => 'EGP', 'numeric_code' => '818', 'name_plural' => 'Egyptian pounds', 'symbol_native' => 'ج.م.‏',],

            35 => ['symbol' => 'Nfk', 'name' => 'Eritrean Nakfa', 'code' => 'ERN', 'numeric_code' => '232', 'name_plural' => 'Eritrean nakfas', 'symbol_native' => 'Nfk',],

            36 => ['symbol' => 'Br', 'name' => 'Ethiopian Birr', 'code' => 'ETB', 'numeric_code' => '230', 'name_plural' => 'Ethiopian birrs', 'symbol_native' => 'Br',],

            37 => ['symbol' => '£', 'name' => 'British Pound Sterling', 'code' => 'GBP', 'numeric_code' => '826', 'name_plural' => 'British pounds sterling', 'symbol_native' => '£',],

            38 => ['symbol' => 'GEL', 'name' => 'Georgian Lari', 'code' => 'GEL', 'numeric_code' => '981', 'name_plural' => 'Georgian laris', 'symbol_native' => 'GEL',],

            39 => ['symbol' => 'GH₵', 'name' => 'Ghanaian Cedi', 'code' => 'GHS', 'numeric_code' => '936', 'name_plural' => 'Ghanaian cedis', 'symbol_native' => 'GH₵',],

            40 => ['symbol' => 'FG', 'name' => 'Guinean Franc', 'code' => 'GNF', 'numeric_code' => '324', 'name_plural' => 'Guinean francs', 'symbol_native' => 'FG',],

            41 => ['symbol' => 'GTQ', 'name' => 'Guatemalan Quetzal', 'code' => 'GTQ', 'numeric_code' => '320', 'name_plural' => 'Guatemalan quetzals', 'symbol_native' => 'Q',],

            42 => ['symbol' => 'HK$', 'name' => 'Hong Kong Dollar', 'code' => 'HKD', 'numeric_code' => '344', 'name_plural' => 'Hong Kong dollars', 'symbol_native' => '$',],

            43 => ['symbol' => 'HNL', 'name' => 'Honduran Lempira', 'code' => 'HNL', 'numeric_code' => '340', 'name_plural' => 'Honduran lempiras', 'symbol_native' => 'L',],

            44 => ['symbol' => 'kn', 'name' => 'Croatian Kuna', 'code' => 'HRK', 'numeric_code' => '191', 'name_plural' => 'Croatian kunas', 'symbol_native' => 'kn',],

            45 => ['symbol' => 'Ft', 'name' => 'Hungarian Forint', 'code' => 'HUF', 'numeric_code' => '348', 'name_plural' => 'Hungarian forints', 'symbol_native' => 'Ft',],

            46 => ['symbol' => 'Rp', 'name' => 'Indonesian Rupiah', 'code' => 'IDR', 'numeric_code' => '360', 'name_plural' => 'Indonesian rupiahs', 'symbol_native' => 'Rp',],

            47 => ['symbol' => '₪', 'name' => 'Israeli New Sheqel', 'code' => 'ILS', 'numeric_code' => '376', 'name_plural' => 'Israeli new sheqels', 'symbol_native' => '₪',],

            48 => ['symbol' => 'Rs', 'name' => 'Indian Rupee', 'code' => 'INR', 'numeric_code' => '356', 'name_plural' => 'Indian rupees', 'symbol_native' => 'টকা',],

            49 => ['symbol' => 'IQD', 'name' => 'Iraqi Dinar', 'code' => 'IQD', 'numeric_code' => '368', 'name_plural' => 'Iraqi dinars', 'symbol_native' => 'د.ع.‏',],

            50 => ['symbol' => 'IRR', 'name' => 'Iranian Rial', 'code' => 'IRR', 'numeric_code' => '364', 'name_plural' => 'Iranian rials', 'symbol_native' => '﷼',],

            51 => ['symbol' => 'Ikr', 'name' => 'Icelandic Króna', 'code' => 'ISK', 'numeric_code' => '352', 'name_plural' => 'Icelandic krónur', 'symbol_native' => 'kr',],

            52 => ['symbol' => 'J$', 'name' => 'Jamaican Dollar', 'code' => 'JMD', 'numeric_code' => '388', 'name_plural' => 'Jamaican dollars', 'symbol_native' => '$',],

            53 => ['symbol' => 'JD', 'name' => 'Jordanian Dinar', 'code' => 'JOD', 'numeric_code' => '400', 'name_plural' => 'Jordanian dinars', 'symbol_native' => 'د.أ.‏',],

            54 => ['symbol' => '¥', 'name' => 'Japanese Yen', 'code' => 'JPY', 'numeric_code' => '392', 'name_plural' => 'Japanese yen', 'symbol_native' => '￥',],

            55 => ['symbol' => 'Ksh', 'name' => 'Kenyan Shilling', 'code' => 'KES', 'numeric_code' => '404', 'name_plural' => 'Kenyan shillings', 'symbol_native' => 'Ksh',],

            56 => ['symbol' => 'KHR', 'name' => 'Cambodian Riel', 'code' => 'KHR', 'numeric_code' => '116', 'name_plural' => 'Cambodian riels', 'symbol_native' => '៛',],

            57 => ['symbol' => 'CF', 'name' => 'Comorian Franc', 'code' => 'KMF', 'numeric_code' => '174', 'name_plural' => 'Comorian francs', 'symbol_native' => 'FC',],

            58 => ['symbol' => '₩', 'name' => 'South Korean Won', 'code' => 'KRW', 'numeric_code' => '410', 'name_plural' => 'South Korean won', 'symbol_native' => '₩',],

            59 => ['symbol' => 'KD', 'name' => 'Kuwaiti Dinar', 'code' => 'KWD', 'numeric_code' => '414', 'name_plural' => 'Kuwaiti dinars', 'symbol_native' => 'د.ك.‏',],

            60 => ['symbol' => 'KZT', 'name' => 'Kazakhstani Tenge', 'code' => 'KZT', 'numeric_code' => '398', 'name_plural' => 'Kazakhstani tenges', 'symbol_native' => 'тңг.',],

            61 => ['symbol' => 'LB£', 'name' => 'Lebanese Pound', 'code' => 'LBP', 'numeric_code' => '422', 'name_plural' => 'Lebanese pounds', 'symbol_native' => 'ل.ل.‏',],

            62 => ['symbol' => 'SLRs', 'name' => 'Sri Lankan Rupee', 'code' => 'LKR', 'numeric_code' => '144', 'name_plural' => 'Sri Lankan rupees', 'symbol_native' => 'SL Re',],

            63 => ['symbol' => 'Lt', 'name' => 'Lithuanian Litas', 'code' => 'LTL', 'numeric_code' => '440', 'name_plural' => 'Lithuanian litai', 'symbol_native' => 'Lt',],

            64 => ['symbol' => 'Ls', 'name' => 'Latvian Lats', 'code' => 'LVL', 'numeric_code' => '428', 'name_plural' => 'Latvian lati', 'symbol_native' => 'Ls',],

            65 => ['symbol' => 'LD', 'name' => 'Libyan Dinar', 'code' => 'LYD', 'numeric_code' => '434', 'name_plural' => 'Libyan dinars', 'symbol_native' => 'د.ل.‏',],

            66 => ['symbol' => 'MAD', 'name' => 'Moroccan Dirham', 'code' => 'MAD', 'numeric_code' => '504', 'name_plural' => 'Moroccan dirhams', 'symbol_native' => 'د.م.‏',],

            67 => ['symbol' => 'MDL', 'name' => 'Moldovan Leu', 'code' => 'MDL', 'numeric_code' => '498', 'name_plural' => 'Moldovan lei', 'symbol_native' => 'MDL',],

            68 => ['symbol' => 'MGA', 'name' => 'Malagasy Ariary', 'code' => 'MGA', 'numeric_code' => '969', 'name_plural' => 'Malagasy Ariaries', 'symbol_native' => 'MGA',],

            69 => ['symbol' => 'MKD', 'name' => 'Macedonian Denar', 'code' => 'MKD', 'numeric_code' => '807', 'name_plural' => 'Macedonian denari', 'symbol_native' => 'MKD',],

            70 => ['symbol' => 'MMK', 'name' => 'Myanma Kyat', 'code' => 'MMK', 'numeric_code' => '104', 'name_plural' => 'Myanma kyats', 'symbol_native' => 'K',],

            71 => ['symbol' => 'MOP$', 'name' => 'Macanese Pataca', 'code' => 'MOP', 'numeric_code' => '446', 'name_plural' => 'Macanese patacas', 'symbol_native' => 'MOP$',],

            72 => ['symbol' => 'MURs', 'name' => 'Mauritian Rupee', 'code' => 'MUR', 'numeric_code' => '480', 'name_plural' => 'Mauritian rupees', 'symbol_native' => 'MURs',],

            73 => ['symbol' => 'MX$', 'name' => 'Mexican Peso', 'code' => 'MXN', 'numeric_code' => '484', 'name_plural' => 'Mexican pesos', 'symbol_native' => '$',],

            74 => ['symbol' => 'RM', 'name' => 'Malaysian Ringgit', 'code' => 'MYR', 'numeric_code' => '458', 'name_plural' => 'Malaysian ringgits', 'symbol_native' => 'RM',],

            75 => ['symbol' => 'MTn', 'name' => 'Mozambican Metical', 'code' => 'MZN', 'numeric_code' => '943', 'name_plural' => 'Mozambican meticals', 'symbol_native' => 'MTn',],

            76 => ['symbol' => 'N$', 'name' => 'Namibian Dollar', 'code' => 'NAD', 'numeric_code' => '516', 'name_plural' => 'Namibian dollars', 'symbol_native' => 'N$',],

            77 => ['symbol' => 'AED', 'name' => 'United Arab Emirates Dirham', 'code' => 'AED', 'numeric_code' => '784', 'name_plural' => 'UAE dirhams', 'symbol_native' => 'د.إ.‏',],

            78 => ['symbol' => 'C$', 'name' => 'Nicaraguan Córdoba', 'code' => 'NIO', 'numeric_code' => '558', 'name_plural' => 'Nicaraguan córdobas', 'symbol_native' => 'C$',],

            79 => ['symbol' => 'Nkr', 'name' => 'Norwegian Krone', 'code' => 'NOK', 'numeric_code' => '578', 'name_plural' => 'Norwegian kroner', 'symbol_native' => 'kr',],

            80 => ['symbol' => 'NPRs', 'name' => 'Nepalese Rupee', 'code' => 'NPR', 'numeric_code' => '524', 'name_plural' => 'Nepalese rupees', 'symbol_native' => 'नेरू',],

            81 => ['symbol' => 'NZ$', 'name' => 'New Zealand Dollar', 'code' => 'NZD', 'numeric_code' => '554', 'name_plural' => 'New Zealand dollars', 'symbol_native' => '$',],

            82 => ['symbol' => 'OMR', 'name' => 'Omani Rial', 'code' => 'OMR', 'numeric_code' => '512', 'name_plural' => 'Omani rials', 'symbol_native' => 'ر.ع.‏',],

            83 => ['symbol' => 'B/.', 'name' => 'Panamanian Balboa', 'code' => 'PAB', 'numeric_code' => '590', 'name_plural' => 'Panamanian balboas', 'symbol_native' => 'B/.',],

            84 => ['symbol' => 'S/.', 'name' => 'Peruvian Nuevo Sol', 'code' => 'PEN', 'numeric_code' => '604', 'name_plural' => 'Peruvian nuevos soles', 'symbol_native' => 'S/.',],

            85 => ['symbol' => '₱', 'name' => 'Philippine Peso', 'code' => 'PHP', 'numeric_code' => '608', 'name_plural' => 'Philippine pesos', 'symbol_native' => '₱',],

            86 => ['symbol' => 'PKRs', 'name' => 'Pakistani Rupee', 'code' => 'PKR', 'numeric_code' => '586', 'name_plural' => 'Pakistani rupees', 'symbol_native' => '₨',],

            87 => ['symbol' => 'zł', 'name' => 'Polish Zloty', 'code' => 'PLN', 'numeric_code' => '985', 'name_plural' => 'Polish zlotys', 'symbol_native' => 'zł',],

            88 => ['symbol' => '₲', 'name' => 'Paraguayan Guarani', 'code' => 'PYG', 'numeric_code' => '600', 'name_plural' => 'Paraguayan guaranis', 'symbol_native' => '₲',],

            89 => ['symbol' => 'QR', 'name' => 'Qatari Rial', 'code' => 'QAR', 'numeric_code' => '634', 'name_plural' => 'Qatari rials', 'symbol_native' => 'ر.ق.‏',],

            90 => ['symbol' => 'RON', 'name' => 'Romanian Leu', 'code' => 'RON', 'numeric_code' => '946', 'name_plural' => 'Romanian lei', 'symbol_native' => 'RON',],

            91 => ['symbol' => 'din.', 'name' => 'Serbian Dinar', 'code' => 'RSD', 'numeric_code' => '941', 'name_plural' => 'Serbian dinars', 'symbol_native' => 'дин.',],

            92 => ['symbol' => 'RUB', 'name' => 'Russian Ruble', 'code' => 'RUB', 'numeric_code' => '643', 'name_plural' => 'Russian rubles', 'symbol_native' => '₽.',],

            93 => ['symbol' => 'RWF', 'name' => 'Rwandan Franc', 'code' => 'RWF', 'numeric_code' => '646', 'name_plural' => 'Rwandan francs', 'symbol_native' => 'FR',],

            94 => ['symbol' => 'SR', 'name' => 'Saudi Riyal', 'code' => 'SAR', 'numeric_code' => '682', 'name_plural' => 'Saudi riyals', 'symbol_native' => 'ر.س.‏',],

            95 => ['symbol' => 'SDG', 'name' => 'Sudanese Pound', 'code' => 'SDG', 'numeric_code' => '938', 'name_plural' => 'Sudanese pounds', 'symbol_native' => 'SDG',],

            96 => ['symbol' => 'Skr', 'name' => 'Swedish Krona', 'code' => 'SEK', 'numeric_code' => '752', 'name_plural' => 'Swedish kronor', 'symbol_native' => 'kr',],

            97 => ['symbol' => 'S$', 'name' => 'Singapore Dollar', 'code' => 'SGD', 'numeric_code' => '702', 'name_plural' => 'Singapore dollars', 'symbol_native' => '$',],

            98 => ['symbol' => 'Ssh', 'name' => 'Somali Shilling', 'code' => 'SOS', 'numeric_code' => '706', 'name_plural' => 'Somali shillings', 'symbol_native' => 'Ssh',],

            99 => ['symbol' => 'SY£', 'name' => 'Syrian Pound', 'code' => 'SYP', 'numeric_code' => '760', 'name_plural' => 'Syrian pounds', 'symbol_native' => 'ل.س.‏',],

            100 => ['symbol' => '฿', 'name' => 'Thai Baht', 'code' => 'THB', 'numeric_code' => '764', 'name_plural' => 'Thai baht', 'symbol_native' => '฿',],

            101 => ['symbol' => 'DT', 'name' => 'Tunisian Dinar', 'code' => 'TND', 'numeric_code' => '788', 'name_plural' => 'Tunisian dinars', 'symbol_native' => 'د.ت.‏',],

            102 => ['symbol' => 'T$', 'name' => 'Tongan Paʻanga', 'code' => 'TOP', 'numeric_code' => '776', 'name_plural' => 'Tongan paʻanga', 'symbol_native' => 'T$',],

            103 => ['symbol' => 'TL', 'name' => 'Turkish Lira', 'code' => 'TRY', 'numeric_code' => '949', 'name_plural' => 'Turkish Lira', 'symbol_native' => 'TL',],

            104 => ['symbol' => 'TT$', 'name' => 'Trinidad and Tobago Dollar', 'code' => 'TTD', 'numeric_code' => '780', 'name_plural' => 'Trinidad and Tobago dollars', 'symbol_native' => '$',],

            105 => ['symbol' => 'NT$', 'name' => 'New Taiwan Dollar', 'code' => 'TWD', 'numeric_code' => '901', 'name_plural' => 'New Taiwan dollars', 'symbol_native' => 'NT$',],

            106 => ['symbol' => 'TSh', 'name' => 'Tanzanian Shilling', 'code' => 'TZS', 'numeric_code' => '834', 'name_plural' => 'Tanzanian shillings', 'symbol_native' => 'TSh',],

            107 => ['symbol' => '₴', 'name' => 'Ukrainian Hryvnia', 'code' => 'UAH', 'numeric_code' => '980', 'name_plural' => 'Ukrainian hryvnias', 'symbol_native' => '₴',],

            108 => ['symbol' => 'USh', 'name' => 'Ugandan Shilling', 'code' => 'UGX', 'numeric_code' => '800', 'name_plural' => 'Ugandan shillings', 'symbol_native' => 'USh',],

            109 => ['symbol' => '$U', 'name' => 'Uruguayan Peso', 'code' => 'UYU', 'numeric_code' => '858', 'name_plural' => 'Uruguayan pesos', 'symbol_native' => '$',],

            110 => ['symbol' => 'UZS', 'name' => 'Uzbekistan Som', 'code' => 'UZS', 'numeric_code' => '860', 'name_plural' => 'Uzbekistan som', 'symbol_native' => 'UZS',],

            111 => ['symbol' => 'Bs.F.', 'name' => 'Venezuelan Bolívar', 'code' => 'VEF', 'numeric_code' => '937', 'name_plural' => 'Venezuelan bolívars', 'symbol_native' => 'Bs.F.',],

            112 => ['symbol' => '₫', 'name' => 'Vietnamese Dong', 'code' => 'VND', 'numeric_code' => '704', 'name_plural' => 'Vietnamese dong', 'symbol_native' => '₫',],

            113 => ['symbol' => 'FCFA', 'name' => 'CFA Franc BEAC', 'code' => 'XAF', 'numeric_code' => '950', 'name_plural' => 'CFA francs BEAC', 'symbol_native' => 'FCFA',],

            114 => ['symbol' => 'CFA', 'name' => 'CFA Franc BCEAO', 'code' => 'XOF', 'numeric_code' => '952', 'name_plural' => 'CFA francs BCEAO', 'symbol_native' => 'CFA',],

            115 => ['symbol' => 'YR', 'name' => 'Yemeni Rial', 'code' => 'YER', 'numeric_code' => '886', 'name_plural' => 'Yemeni rials', 'symbol_native' => 'ر.ي.‏',],

            116 => ['symbol' => 'R', 'name' => 'South African Rand', 'code' => 'ZAR', 'numeric_code' => '710', 'name_plural' => 'South African rand', 'symbol_native' => 'R',],

            117 => ['symbol' => 'ZK', 'name' => 'Zambian Kwacha', 'code' => 'ZMK', 'numeric_code' => '894', 'name_plural' => 'Zambian kwachas', 'symbol_native' => 'ZK',],

            118 => ['symbol' => 'ZWL$', 'name' => 'Zimbabwean Dollar', 'code' => 'ZWL', 'numeric_code' => '932', 'name_plural' => 'Zimbabwean Dollar', 'symbol_native' => 'ZWL$'],
        ];
        foreach ($currencies as $currency) {
            $selected = '';
            $name = $currency['name'];
            $symbol = $currency['symbol_native'];
            $code = $currency['code'];
            if ($keyValue === $code) {
                $selected = 'selected';
            }
            $title = $name . " ( $symbol ) ";
            $frag .= <<<FRAG
<option $selected title="$title" value="$code">$title</option>
FRAG;
        }

        $frag .= <<<Frag
    </select>
</div>
Frag;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}
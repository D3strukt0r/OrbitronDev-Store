<?php

/*
 * British Pounds = GBP,
 * US Dollars = USD,
 * Euros = EUR,
 * Australian Dollars = AUD,
 * Bulgarian Leva = BGN,
 * Canadian Dollars = CAD,
 * Swiss Francs = CHF,
 * Chinese Yuan Renminbi = CNY,
 * Cyprian Pounds = CYP,
 * Czech Koruny = CZK,
 * Danish Kroner = DKK,
 * Estonian Krooni = EEK,
 * Hong Kong,
 * Dollars = HKD,
 * Croatian Kuna = HRK,
 * Hungarian Forint = HUF,
 * Indonesian Rupiahs = IDR,
 * Icelandic Kronur = ISK,
 * Japanese Yen = JPY,
 * South Korean Won = KRW,
 * Lithuanian Litai = LTL,
 * Latvian Lati = LVL,
 * Malta Liri = MTL,
 * Malaysian Ringgits = MYR,
 * Norwegian Krone = NOK,
 * New Zealand Dollars = NZD,
 * Philippine Pesos = PHP,
 * Polish Zlotych = PLN,
 * Romanian New Lei = RON,
 * Russian Rubles = RUB,
 * Swedish Kronor = SEK,
 * Slovenian Tolars = SIT,
 * Slovakian Koruny = SKK,
 * Thai Baht = THB,
 * Turkish New Lira = TRY,
 * South African Rand = ZAR
 */

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ECBCurrencyConverter
{
    private $sXmlFile = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    private $sCachedFile = '/var/data/currency/currency-data.xml';

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    public function __construct(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
        $this->sCachedFile = $this->kernel->getProjectDir().'/var/data/currency/currency-data.xml';
    }

    public function update()
    {
        if (!file_exists($this->sCachedFile)) {
            $this->download($this->sCachedFile);

            return true;
        }

        $oCurrencyDataLocal = simplexml_load_file($this->sCachedFile);
        $oCurrencyDataHosted = simplexml_load_file($this->sXmlFile);

        if ($oCurrencyDataLocal->Cube->Cube['time'] !== $oCurrencyDataHosted->Cube->Cube['time']) {
            $this->download($this->sCachedFile);

            return true;
        }

        return false;
    }

    private function download($save_to)
    {
        if (!file_exists(dirname($save_to))) {
            mkdir(dirname($save_to), 0777, true);
        }
        file_put_contents($save_to, fopen($this->sXmlFile, 'r'));
    }

    /***********************************************************************************/

    /**
     * @param string $currency
     *
     * @return float
     */
    private function getRate($currency)
    {
        $oXmlFile = simplexml_load_file($this->sCachedFile);
        foreach ($oXmlFile->Cube->Cube->Cube as $aRate) {
            if (mb_strtoupper($currency) === mb_strtoupper($aRate['currency'])) {
                return (float) $aRate['rate'];
            }
        }

        return 'currency_does_not_exists';
    }

    /**
     * Perform the actual conversion
     * Hint: Base is EUR, so everything is converted to EUR and then to the given currency.
     *
     * @param float  $amount   (Required) How much should be converted
     * @param string $from     (Required) From which currency should be converted
     * @param string $to       (Optional) To which currency should be converted. Default is USD.
     * @param int    $decimals (Optional) How much decimals should the number have
     *
     * @return float
     */
    public function convert(float $amount, string $from, string $to = 'USD', int $decimals = 2): float
    {
        if (!file_exists($this->sCachedFile)) {
            self::download($this->sCachedFile);
        }

        if ('currency_does_not_exists' === $this->getRate($from) || ('EUR' !== mb_strtoupper($to) && 'currency_does_not_exists' === $this->getRate($to))) {
            return $this->translator->trans('Currency not found');
        }

        $currency = $amount / $this->getRate($from);
        if ('EUR' !== mb_strtoupper($to)) {
            $currency = $currency * $this->getRate($to);
        }

        return number_format($currency, $decimals);
    }
}

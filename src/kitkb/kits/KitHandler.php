<?php
/**
 * Created by PhpStorm.
 * User: jkorn2324
 * Date: 2019-07-01
 * Time: 18:56
 */

declare(strict_types=1);

namespace kitkb\kits;

use kitkb\KitKb;
use pocketmine\Player;
use pocketmine\utils\Config;

class KitHandler
{

    /* @var string */
    private $path;

    /* @var array|Kit[] */
    private $kits;

    /* @var Config */
    private $config;

    /**
     * KitHandler constructor.
     * @param KitKb $kb
     */
    public function __construct(KitKb $kb)
    {
        $this->path = $kb->getDataFolder() . '/kits.yml';
        $this->kits = [];
        $this->initConfig();
    }

    /**
     * Initializes the configuration of the kit handler & initializes the kits.
     */
    private function initConfig() {

        $this->config = new Config($this->path, Config::YAML, []);

        if(!file_exists($this->path)) {
            $this->config->save();
        } else {
            $keys = $this->config->getAll(true);
            foreach($keys as $key) {
                $kit = $this->parseKit((string)$key);
                if($kit !== null) {
                    $this->kits[$key] = $kit;
                }
            }
        }
    }

    /**
     * @param string $name
     * @return Kit|null
     */
    private function parseKit(string $name) {

        $kit = null;

        if($this->config->exists($name)) {

            $value = $this->config->get($name);

            if(isset($value['items'], $value['armor'], $value['effects'], $value['kb'])) {

                $itemData = $value['items'];
                $armorData = $value['armor'];
                $effectsData = $value['effects'];
                $kbData = $value['kb'];

                $items = [];
                foreach($itemData as $slot => $iData) {
                    $item = KitKb::strToItem(strval($iData));
                    $items[(int)$slot] = $item;
                }

                $armor = [];
                foreach($armorData as $key => $value) {
                    $index = KitKb::getArmorStr($key);
                    $armor[$index] = KitKb::strToItem($value);
                }

                $effects = [];
                foreach($effectsData as $eData) {
                    $effect = KitKb::strToEffect($eData);
                    $effects[] = $effect;
                }

                $kb = null;
                if(isset($kbData['xkb'], $kbData['ykb'], $kbData['speed'])) {
                    $speed = intval($kbData['speed']);
                    $yKb = floatval($kbData['ykb']);
                    $xKb = floatval($kbData['xkb']);
                    $kb = new KbInfo($xKb, $yKb, $speed);
                }

                $kit = new Kit($name, $items, $armor, $effects, $kb);
            }
        }
        return $kit;
    }


    /**
     * @param string $name
     * @param Player $player
     * @param float $xkb
     * @param float $ykb
     * @param int $speed
     *
     * Creates a new kit.
     */
    public function createKit(string $name, $player, float $xkb = 0.4, float $ykb = 0.4, int $speed = 10) {

        $invArr = KitKb::inventoryToArray($player);
        $effects = array_values($player->getEffects()->all());
        $kbInfo = new KbInfo($xkb, $ykb, $speed);
        $kit = new Kit($name, $invArr['items'], $invArr['armor'], $effects, $kbInfo);

        if(!$this->config->exists($name)) {
            $map = $kit->toArray();
            $this->config->set($name, $map);
            $this->config->save();
        }

        $this->kits[$name] = $kit;
    }

    /**
     * @param Kit $kit
     * 
     * Updates the kit.
     */
    public function updateKit(Kit $kit) 
    {
        $name = $kit->getName();
        if(!isset($this->kits[$name]))
        {
            return;
        }

        $this->kits[$name] = $kit;
        $this->config->set($name, $kit->toArray());
        $this->config->save();
    }

    /**
     * @param string $name
     *
     * Deletes the kits.
     */
    public function deleteKit(string $name) {

        if(isset($this->kits[$name])) {
            unset($this->kits[$name]);
        }

        if($this->config->exists($name)) {
            $this->config->remove($name);
            $this->config->save();
        }
    }

    /**
     * @param string $kit
     * @return Kit|null
     *
     * Gets the kit based on its name.
     */
    public function getKit(string $kit) {
        foreach($this->kits as $name => $kitObj) {
            if(strtolower($name) === strtolower($kit)) {
                return $kitObj;
            }
        }
        return null;
    }

    /**
     * @param string $kit
     * @return bool
     *
     * Determines if it's a kit.
     */
    public function isKit(string $kit) {
        foreach($this->kits as $name => $_) {
            if(strtolower($name) === strtolower($kit)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Kit[]
     *
     * Gets the kits from the list.
     */
    public function getKits()
    {
        return $this->kits;
    }

    public function reloadConfig(): void {
        $this->kits = [];
        $this->initConfig();
    }
}
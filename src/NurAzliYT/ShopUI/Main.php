<?php

namespace NurAzliYT\ShopUi;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class ShopUIPlugin extends PluginBase implements Listener {

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();

        if($item->getId() === Item::DIAMOND){
            $this->openShopUI($player);
            $event->setCancelled();
        }
    }

    public function openShopUI(Player $player){
        $ui = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, array $data) {
            if(isset($data[0])){
                switch($data[0]){
                    case 0:
                        $this->showCategoryItems($player, "Sword");
                        break;
                    case 1:
                        $this->showCategoryItems($player, "Block");
                        break;
                    // Add more cases for other categories
                }
            }
        });
        $ui->setTitle("Shop");
        $ui->setContent("Select a category:");
        $ui->addButton("Sword");
        $ui->addButton("Block");
        // Add more buttons for other categories
        $player->sendForm($ui);
    }

    public function showCategoryItems(Player $player, string $category){
        $inventory = $player->getInventory();
        $ui = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, array $data) use ($category) {
            if(isset($data[0])){
                $itemName = $data[0];
                // Handle purchase based on item name and category here
                // For example:
                switch($category){
                    case "Sword":
                        $this->buyItem($player, $itemName, [
                            "Iron Sword" => ["id" => Item::IRON_SWORD, "price" => 5],
                            "Gold Sword" => ["id" => Item::GOLD_SWORD, "price" => 10],
                        ]);
                        break;
                    case "Block":
                        $this->buyItem($player, $itemName, [
                            "Stone Block" => ["id" => Item::STONE, "price" => 1],
                            "Wood Block" => ["id" => Item::WOOD, "price" => 2],
                        ]);
                        break;
                    // Add more cases for other categories
                }
            }
        });
        $ui->setTitle($category);
        $ui->setContent("Select an item to purchase:");
        
        // Depending on the category, add appropriate items to the UI
        switch($category){
            case "Sword":
                $ui->addButton("Iron Sword\nPrice: 5 Diamonds");
                $ui->addButton("Gold Sword\nPrice: 10 Diamonds");
                // Add more buttons for sword items
                break;
            case "Block":
                $ui->addButton("Stone Block\nPrice: 1 Diamond");
                $ui->addButton("Wood Block\nPrice: 2 Diamonds");
                // Add more buttons for block items
                break;
            // Add more cases for other categories
        }
        
        $player->sendForm($ui);
    }

    public function buyItem(Player $player, string $itemName, array $items){
        $inventory = $player->getInventory();
        $diamonds = $inventory->getItem(Item::DIAMOND);

        if(isset($items[$itemName])){
            $itemInfo = $items[$itemName];
            $price = $itemInfo["price"];
            $itemId = $itemInfo["id"];

            if($diamonds->getCount() >= $price){
                $diamonds->setCount($diamonds->getCount() - $price);
                $inventory->setItem($inventory->first($diamonds), $diamonds);
                $inventory->addItem(Item::get($itemId));
                $player->sendMessage("You've purchased ".$itemName);
            }else{
                $player->sendMessage("Not enough diamonds to purchase this item.");
            }
        }
    }
}

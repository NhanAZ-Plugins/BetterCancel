<?php

declare(strict_types=1);

namespace NhanAZ\BetterCancel;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\EventPriority;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	protected function onEnable(): void {
		$manager = $this->getServer()->getPluginManager();

		$handler = static function (BlockBreakEvent|BlockPlaceEvent $event): void {
			if (!$event->isCancelled()) {
				return;
			}
			$player = $event->getPlayer();
			$session = $player->getNetworkSession();
			$session->sendDataPacket(DenySound::getPacket($player->getLocation()));
			if ($event instanceof BlockBreakEvent) {
				$session->sendDataPacket(ForceFieldParticle::getPacket($event->getBlock()->getPosition()));
				return;
			}
			foreach ($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
				$session->sendDataPacket(ForceFieldParticle::getPacket($block->getPosition()));
				break;
			}
		};

		$manager->registerEvent(
			event: BlockBreakEvent::class,
			handler: $handler,
			priority: EventPriority::MONITOR,
			plugin: $this,
			handleCancelled: true
		);

		$manager->registerEvent(
			event: BlockPlaceEvent::class,
			handler: $handler,
			priority: EventPriority::MONITOR,
			plugin: $this,
			handleCancelled: true
		);
	}
}

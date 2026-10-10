<?php

declare(strict_types=1);

namespace NhanAZ\BetterCancel;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;

final class ForceFieldParticle {

	public static function getPacket(Vector3 $vector3): LevelEventPacket {
		return LevelEventPacket::create(
			eventId: LevelEvent::PARTICLE_BLOCK_FORCE_FIELD,
			eventData: 0,
			position: $vector3->add(0.5, 0.5, 0.5)
		);
	}
}

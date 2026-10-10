<?php

declare(strict_types=1);

namespace pocketmine\math {
	class Vector3 {
		public function __construct(public float $x, public float $y, public float $z) {}
		public function add(float $x, float $y, float $z): self { return new self($this->x + $x, $this->y + $y, $this->z + $z); }
	}
}

namespace pocketmine\world\sound {
	interface Sound { public function encode(\pocketmine\math\Vector3 $pos): array; }
}

namespace pocketmine\network\mcpe\protocol\types {
	final class LevelEvent { public const PARTICLE_BLOCK_FORCE_FIELD = 1; }
	final class LevelSoundEvent { public const DENY = 2; }
}

namespace pocketmine\network\mcpe\protocol {
	final class LevelEventPacket {
		public static function create(int $eventId, int $eventData, \pocketmine\math\Vector3 $position): self { return new self($eventId, $position); }
		private function __construct(public int $eventId, public \pocketmine\math\Vector3 $position) {}
	}
	final class LevelSoundEventPacket {
		public static function nonActorSound(int $soundId, \pocketmine\math\Vector3 $position, bool $isBabyMob): self { return new self($soundId); }
		private function __construct(public int $soundId) {}
	}
}

namespace pocketmine\event {
	final class EventPriority { public const MONITOR = 5; }
}

namespace pocketmine\player {
	final class NetworkSession {
		/** @var list<object> */
		public array $packets = [];
		public function sendDataPacket(object $packet): void { $this->packets[] = $packet; }
	}
	final class Player {
		public function __construct(public NetworkSession $session) {}
		public function getLocation(): \pocketmine\math\Vector3 { return new \pocketmine\math\Vector3(10, 20, 30); }
		public function getNetworkSession(): NetworkSession { return $this->session; }
	}
}

namespace pocketmine\block {
	final class Block {
		public function __construct(private \pocketmine\math\Vector3 $position) {}
		public function getPosition(): \pocketmine\math\Vector3 { return $this->position; }
	}
}

namespace pocketmine\event\block {
	class BlockBreakEvent {
		public function __construct(private \pocketmine\player\Player $player, private \pocketmine\block\Block $block, private bool $cancelled) {}
		public function isCancelled(): bool { return $this->cancelled; }
		public function getPlayer(): \pocketmine\player\Player { return $this->player; }
		public function getBlock(): \pocketmine\block\Block { return $this->block; }
	}
	final class Transaction {
		/** @param list<array{int, int, int, \pocketmine\block\Block}> $blocks */
		public function __construct(private array $blocks) {}
		public function getBlocks(): array { return $this->blocks; }
	}
	class BlockPlaceEvent {
		public function __construct(private \pocketmine\player\Player $player, private Transaction $transaction, private bool $cancelled) {}
		public function isCancelled(): bool { return $this->cancelled; }
		public function getPlayer(): \pocketmine\player\Player { return $this->player; }
		public function getTransaction(): Transaction { return $this->transaction; }
	}
}

namespace pocketmine\plugin {
	final class Manager {
		/** @var array<string, \Closure> */
		public array $handlers = [];
		public function registerEvent(string $event, \Closure $handler, int $priority, object $plugin, bool $handleCancelled): void {
			$this->handlers[$event] = $handler;
		}
	}
	class PluginBase {
		public static Manager $manager;
		public function getServer(): object { return new class {
			public function getPluginManager(): Manager { return PluginBase::$manager; }
		}; }
	}
}

namespace {
	require __DIR__ . '/../src/NhanAZ/BetterCancel/DenySound.php';
	require __DIR__ . '/../src/NhanAZ/BetterCancel/ForceFieldParticle.php';
	require __DIR__ . '/../src/NhanAZ/BetterCancel/Main.php';

	use NhanAZ\BetterCancel\Main;
	use pocketmine\block\Block;
	use pocketmine\event\block\BlockBreakEvent;
	use pocketmine\event\block\BlockPlaceEvent;
	use pocketmine\event\block\Transaction;
	use pocketmine\math\Vector3;
	use pocketmine\network\mcpe\protocol\LevelEventPacket;
	use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
	use pocketmine\player\NetworkSession;
	use pocketmine\player\Player;
	use pocketmine\plugin\PluginBase;

	PluginBase::$manager = new \pocketmine\plugin\Manager();
	(new ReflectionMethod(Main::class, 'onEnable'))->invoke(new Main());
	$session = new NetworkSession();
	$player = new Player($session);
	$first = new Block(new Vector3(1, 2, 3));
	$second = new Block(new Vector3(4, 5, 6));
	$break = PluginBase::$manager->handlers[BlockBreakEvent::class];
	$place = PluginBase::$manager->handlers[BlockPlaceEvent::class];
	$break(new BlockBreakEvent($player, $first, false));
	if ($session->packets !== []) { throw new RuntimeException('Sent a packet for an allowed event'); }
	$break(new BlockBreakEvent($player, $first, true));
	$place(new BlockPlaceEvent($player, new Transaction([[1, 2, 3, $first], [4, 5, 6, $second]]), true));
	if (count($session->packets) !== 4 || !$session->packets[0] instanceof LevelSoundEventPacket || !$session->packets[1] instanceof LevelEventPacket || !$session->packets[2] instanceof LevelSoundEventPacket || !$session->packets[3] instanceof LevelEventPacket) {
		throw new RuntimeException('Cancelled events did not send one sound and particle each');
	}
	foreach ([1, 3] as $index) {
		$point = $session->packets[$index]->position;
		if ([$point->x, $point->y, $point->z] !== [1.5, 2.5, 3.5]) { throw new RuntimeException('Particle was not centered on the first affected block'); }
	}
	echo "Cancelled break and place packet checks passed\n";
}

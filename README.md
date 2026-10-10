# BetterCancel

BetterCancel sends a denial sound and force field particle to a player when a block break or placement event is cancelled on an Axolotl-PM server.

## How it works

The plugin observes cancelled `BlockBreakEvent` and `BlockPlaceEvent` calls. It sends the denial sound to the affected player and centers a force field particle on the broken block or the first block in the placement transaction. It does not change which actions are allowed or cancelled.

The [original animation](https://user-images.githubusercontent.com/60387689/180753839-54a3b8c5-7e87-46f2-931a-0e96187c0c80.gif) shows the intended client effect. Visual appearance depends on the Bedrock client and has not been verified in this release campaign.

## Requirements and compatibility

BetterCancel declares Axolotl-PM API `5.0.0` and requires PHP 8.1 or newer. It has no separate runtime library dependency. PHPStan at maximum level passes against Axolotl-PM source revisions 5.0.0 and the revision pinned in the [build workflow](.github/workflows/build.yml). The release PHAR has been smoke tested on the latter revision. Intermediate 5.x releases and connected Bedrock clients have not been verified.

## Installation

Download `BetterCancel.phar` from [GitHub releases](https://github.com/NhanAZ-Plugins/BetterCancel/releases), place it in the server's `plugins/` directory, and restart the server. Another plugin or the server must cancel a block action for BetterCancel to send the effect. There are no configuration options or commands.

## Building and testing

The [build workflow](.github/workflows/build.yml) runs `php tests/cancel-events.php`, PHPStan at maximum level, and [DevTools](https://github.com/NhanAZ/DevTools). DevTools builds the PHAR and checks its checksum and license. The local event fixture checks cancelled and allowed actions and the selected block position. It does not connect a Bedrock client.

## License and support

BetterCancel retains its [GPL-3.0 license](LICENSE). For support, use [NhanAZ Discord](https://discord.gg/j2X83ujT6c).

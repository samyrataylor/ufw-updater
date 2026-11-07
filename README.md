# UFW Updater

Manages (specifically ssh) UFW rules and ensures that only allowed IPs are added to the rule list. Supports looking up domains and resolving the IP address and allowing those through (use case for dynamic dns).

## Install

Download `ufw-updater` from the latest [release](https://github.com/samyrataylor/ufw-updater/releases/latest)

## Configure

The config file is not published by default and will not touch your rules by default

### Generate Config

```bash
./ufw-updater config:generate
```

Generates `config.json` in the same directory as the application. 

#### Default config.json

```json
{
    "allowed_ips": [],
    "domains": [],
    "remove_others": false,
    "remove_allow_any": false,
    "protocol": "tcp",
    "port": 22
}
```

#### Config Reference

**allowed_ips**

An array containing IP addresses to add to UFW allow list. 

|Value|Example|Parses To|Description|
|:-:|:-:|:-:|:-|
|(any IP address)|`"192.168.0.1"`|`"192.168.0.1"`|Any single IP address|
|(CIDR notation IP)|`"192.168.0.0/24"`|`"192.168.0.0/24"`|Any valid IP range using [CIDR notation](https://www.ipaddressguide.com/cidr)|
|`"any"`|`"any"`|`"Anywhere"`|Allows from anywhere (not recommended)|

**domains**

An array containing domains that will be resolved to an IP address to add.

|Value|Example|Parses To|Description|
|:-:|:-:|:-:|:-|
|(any domain)|`"github.com"`|`"140.82.113.4"`|Any domain (do not include https://)|

**remove_others**

Boolean value:

|Value|Description|
|:-:|:-|
|`true`|Will remove all UFW rules with the port & protocol specified in `port` & `protocol` before adding new rules|
|`false`|Leaves existing UFW rules as is (default)|

**remove_allow_any**

Boolean value:

|Value|Description|
|:-:|:-|
|`true`|Will remove UFW rules with `Anywhere` as source with the port & protocol specified in `port` & `protocol` before adding new rules|
|`false`|Leaves existing UFW rules as is (default)|

Note: if `remove_others` is `true` these would be removed in that step. 

**protocol**

Can be `"tcp"`, `"udp"`, `"any"` or `null`

`"any"` and `null` result in any protocol.

**port**

Integer of the port to manage UFW rules for. Will not touch others.

### Show Configuration

```bash
./ufw-updater config:show
```

Displays the loaded config from `config.json` as JSON

## Update Rules

```bash
./ufw-updater rules:update
```

Update UFW rules per configuration

# Additional Commands

## List Rules

Displays UFW rules

```bash
./ufw-updater rules:list
```

By default will display in a formatted table like

|Number|Port|Protocol|Policy|Direction|Source|v6|
|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
|1|22|TCP|ALLOW|IN|192.168.0.1|✘|
|2|22|TCP|ALLOW|IN|192.168.0.0/24|✘|
|3|22|TCP|ALLOW|IN|140.82.113.4|✘|
|4|22|TCP|ALLOW|IN|any|✘|
|5|22|TCP|ALLOW|IN|any|✔|

### Command Options

|Option|Description|
|:-|:-|
|`-a\|--all`|List all rules, not just filtered by `port` and `protocol`|
|`-u\|--ufw`|Rather than parsing rules, display rule as it appears in `ufw status`|
|`-r\|--raw`|Don't format into a table (rules each on new line)|
|`-j\|--json`|JSON output, `--raw` is implied|
|`-c\|--compact`|Compact JSON output (don't pretty print), only if `--json` option is present|
|`-f\|--flat`|Flatten JSON output, only if `--json` option is present, useful with `--ufw` option|
|`-b\|--no-numbers`|Don't include the rule numbers in the output|

## Version

Prints version and exits

```bash
./ufw-updater version
```

## Self Update

Updates itself to the latest version

```bash
./ufw-updater self-update
```

## Install

Adds a cron job to run `rules:update` on specific schedule

```bash
./ufw-updater install
```

- By default the schedule is every day at 2 AM (`0 2 * * *`).
- If the same job already exists, it will not be added again.
- If the same job with a different schedule exists, it will prompt to update to new schedule provided

### Options

|Option|Description|Example|
|:-|:-|:-|
|`-y\|--yes`|Responds yes to all prompts| |
|`-s=\|--schedule=`|Specify a different schedule using cron format, surround in quotes|`--schedule="0 5 * * *"`|

## Auto Update

Adds a cron job to run `self-update` on specific schedule

```bash
./ufw-updater auto-update
```

- By default the schedule is every day at 1 AM (`0 1 * * *`)
- Options are the same as the `install` command above





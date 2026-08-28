# iTerm2 Network-Aware Profile Watchdog

A small macOS `launchd` watchdog that keeps a specific iTerm2 profile running whenever the Mac is connected to a network **other than the configured home network**.

I use this to keep a tunnel available automatically so I can use services I don't want exposed to the internet (in my case, mainly samba and RDP) when I'm at the office, a coffee shop, a hotel, tethered to my phone, or anywhere else away from home. On my home network, where the tunnel isn't needed, the watchdog leaves it alone.

## What it does

Every 20 seconds, the script:

1. Determines whether the Mac has an active network/default route.
2. Checks whether the current network is the configured home network using:
   - Wi-Fi SSID, or
   - Default gateway, for wired connections and as a general fallback.
3. If the Mac is at home, does nothing.
4. If the Mac is on any other network, checks all open iTerm2 sessions for the configured profile.
5. If the profile is already running, does nothing.
6. If it isn't running:
   - Opens it as a new tab in the current iTerm2 window, or
   - Opens a new iTerm2 window if no window currently exists.

This is intentionally a **home-network denylist** rather than an office-network allowlist. The tunnel therefore works automatically on networks that weren't known when the script was configured.

## Files

```text
~/bin/proxy-up
~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

The filenames can be changed; just make sure the path in the LaunchAgent matches the script location.

## Configuration

The relevant values are at the top of `proxy-up`:

```zsh
PROFILE="iTerm Proxy Profile"
HOME_SSID="Home_SSID"
HOME_GATEWAY="192.168.1.1"
```

### `PROFILE`

The exact name of the iTerm2 profile that should be kept running.

### `HOME_SSID`

The Wi-Fi SSID where the profile should **not** be started.

### `HOME_GATEWAY`

The default gateway that identifies the home network.

This handles wired Ethernet as well as cases where macOS doesn't expose the current Wi-Fi SSID to the background process.

To determine the current default gateway on macOS:

```bash
/sbin/route -n get default | awk '/gateway:/ { print $2 }'
```

## Network Behavior

```text
Home Wi-Fi (SSID matches)
    → do nothing

Home Ethernet (gateway matches)
    → do nothing

Office Wi-Fi
    → run tunnel

Office Ethernet
    → run tunnel

Coffee shop / hotel / client network
    → run tunnel

Phone hotspot
    → run tunnel

No network / no default route
    → do nothing
```

A captive-portal network may cause the tunnel session to fail until the portal login is completed. The watchdog will continue checking, so the tunnel should be recreated automatically once normal connectivity is available.

## Installation

Copy the script to a location in your `$PATH`, e.g.:

```bash
mkdir -p ~/bin
cp proxy-up ~/bin/proxy-up
```

Copy the LaunchAgent plist to your `~/Library/LaunchAgents` folder:

```bash
cp iterm-proxy-watchdog.plist ~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

Make the script executable:

```bash
chmod +x ~/bin/proxy-up
```

Validate the LaunchAgent plist:

```bash
plutil -lint \
    ~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

Load it:

```bash
launchctl bootstrap \
    gui/$(id -u) \
    ~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

Force an immediate run if desired:

```bash
launchctl kickstart -k \
    gui/$(id -u)/iterm-proxy-watchdog
```

## Testing

Before enabling the LaunchAgent, the script can be run manually:

```bash
~/bin/proxy-up
```

### Away from home

On any network other than the configured home network:

- The configured profile should open as a new tab.
- Running the script again should do nothing.
- Closing the profile's tab and running the script again should recreate it.

### At home

On the configured home network:

```bash
~/bin/proxy-up
```

should do nothing.

### Debugging

Trace what the script is doing:

```bash
zsh -x ~/bin/proxy-up
```

Verify shell syntax:

```bash
zsh -n ~/bin/proxy-up
```

No output from `zsh -n` means the script parses successfully.

Check the currently detected default gateway:

```bash
/sbin/route -n get default | awk '/gateway:/ { print $2 }'
```

## Unloading / Reloading

Unload the LaunchAgent:

```bash
launchctl bootout \
    gui/$(id -u) \
    ~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

Reload it:

```bash
launchctl bootstrap \
    gui/$(id -u) \
    ~/Library/LaunchAgents/iterm-proxy-watchdog.plist
```

Changes to `proxy-up` itself don't require reloading the LaunchAgent. Changes to the `.plist` do.

## iTerm2 Session Behavior

The iTerm2 profile should be configured to **close when its command exits**, rather than automatically restarting itself.

The watchdog is responsible for deciding whether the session should be restarted based on the current network.

```text
proxy/session dies
        ↓
iTerm closes the tab
        ↓
watchdog runs
        ↓
active network?
   no → leave closed
   yes
    ↓
home network?
   yes → leave closed
   no
    ↓
profile already running?
   yes → do nothing
   no  → reopen
```

The script does not focus or select an existing proxy tab when it runs. If the configured profile already exists anywhere in iTerm2, the watchdog silently leaves it alone.

## macOS Notes

### `route` location

On macOS, `route` is:

```text
/sbin/route
```

not:

```text
/usr/sbin/route
```

This matters when using absolute command paths from a `launchd` script.

### AppleScript heredocs

If modifying the script's embedded AppleScript, remember that a standard shell heredoc terminator must begin at column 1:

```zsh
/usr/bin/osascript <<'APPLESCRIPT'
tell application "iTerm2"
    -- stuff
end tell
APPLESCRIPT
```

Indenting the closing `APPLESCRIPT` will cause zsh to treat the rest of the file as part of the heredoc.

## Why `launchd`?

The iTerm2 profile itself could be configured to restart automatically, but it has no awareness of the network the Mac is currently using.

Keeping that policy outside iTerm makes the desired behavior explicit:

> Keep this connection alive whenever I'm away from home. At home, leave it alone.

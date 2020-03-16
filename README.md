# CAH Faculty Information Card
A quick plugin that provides a single faculty member's directory entry (with headshot), pulled from Manager.

## How to Use
The shortcode to use the plugin is `[cah-faculty-card]`. At this time, it has a few possible attributes:
* `id`: The userID of the faculty member to be displayed. Default is `0`, which won't pull up anyone.
* `class`: Any class(es) to be added to the card div. Default is an empty string.
* `img_shape`: Uses `rounded` for a rounded square, and `circle` for a circular headshot. Default `rounded`.
* `interests`: Boolean value for whether to display faculty interests. Default `false`. *(Note: Not yet implemented)*
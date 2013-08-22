&lt;?php
/**
 * This script was automatically generated. Instead of modifying it directly,
 * the best practice is to modify the corresponding &lt;config&gt; element in the
 * Typeframe registry and regenerate this script with the tfadmin.php tool.
 *
 * The primary purpose of this script is to document the constants defined in
 * the application registry so they are discoverable in IDEs.
 */
 
<pm:loop name="defines" as="d">
/**
 * @{d->caption}@ (default: '@{d->default}@')
 */
define('@{d->name}@', Typeframe::Registry()->getConfigValue('@{d->name}@'));
</pm:loop>

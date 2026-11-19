# Apple design catalog

How sedovi.nl builds each pattern. Static HTML, one shared stylesheet. Read this before changing the site.

## Reference screens

| Screen | File | Reference |
| --- | --- | --- |
| Home, Dutch | `index.html` | apple.com product pages: big headline, sections that each make one point |
| Home, English | `en/index.html` | same markup as the Dutch page, strings translated |

## Tokens

All tokens are on `:root` in `assets/site.css`: `--bg`, `--surface`, `--ink`, `--muted`, `--line`, `--blue`, `--blue-ink`, the type steps `--step-1` to `--step-4`, `--gutter`, `--max`, `--section`, `--radius`, `--radius-lg`, `--pill`, `--shadow`. Fonts are Hanken Grotesk (text), Schibsted Grotesk (display) and Space Mono (labels). Never hardcode a color, size or radius in the HTML.

## Patterns

### Grouped list (FAQ)
- Apple source: Settings grouped rows, https://developer.apple.com/design/human-interface-guidelines/lists-and-tables
- Markup: `.faq > details > summary + p`. Rows are at least 60px tall, separated by `--line`, on a `--surface` card.

### Project card
- Custom. A white card with a 16:10 media area on top (browser frame, phone frame overlapping on the right) and text below. Demo-data notes go in the card body as `p.note`.
- Cards sit in a horizontal row (`.projects`, closest to the apple.com "Get to know" shelves), so the page doesn't grow taller with each new project. Two cards fit side by side from 1000px. Under that each card is min(86vw, 560px) wide. The row bleeds to the viewport edge through `--inset` but snaps cards to the page gutter.
- `.rail-head` holds the heading and the round prev/next buttons. `site.js` hides the buttons when everything fits and disables them at each end. To add a project, add one more `article.project`; no other change is needed (update the "Twee producten / Two products" lede).

### Service card with price
- Custom, closest to apple.com "compare models" cards. Mono "from" label, display-font price, one-line description. No feature list until the owner approves package contents.

### Numbered steps
- Custom. `ol.steps` with mono counters, 4 columns, 2 on tablet, 1 on phone.

## Page skeleton

Sticky blurred header (logo, section links, language pill, Contact button) → typographic hero (no background animation) → Work → Services → Process and FAQ → blue contact panel → footer with company details and language links.

## Rules

- Buttons and links are at least 44px tall.
- Every string exists in both `index.html` and `en/index.html`.
- No em dashes in copy.

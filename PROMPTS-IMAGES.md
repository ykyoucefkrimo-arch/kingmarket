# Prompts pour générer les visuels du site

Style de référence : coque noire mate/texturée type "rugged", écran e-ink
carré intégré au dos affichant une œuvre abstraite façon coup de pinceau
noir/blanc avec touches de rouge et jaune, éclairage studio produit haut de
gamme, fond neutre clair.

Génère ces images avec l'outil de ton choix (Bing Image Creator, ChatGPT/DALL-E,
Midjourney, Leonardo.ai...), puis donne-moi les fichiers pour que je les
intègre dans le site (remplace les balises `<div class="img-placeholder">`
par des `<img>`).

---

## 1. Hero (image principale, la plus importante)
**Emplacement** : `index.html`, section `.hero-visual`
**Format recommandé** : carré ou portrait légèrement (ex. 1000×1200px), fond clair/neutre pour matcher le site

> Professional product photography of a matte black rugged smartphone case
> for a large iPhone, photographed at a 3/4 angle. The back of the case
> features a built-in square e-ink display showing an abstract black brush-
> stroke artwork with accents of red and yellow, similar to bold minimalist
> art. Raised camera bump with reinforced black frame around triple camera
> lenses. Studio lighting, soft shadows, premium tech product shot, clean
> light gray or off-white background, high detail, realistic materials
> (textured TPU + glass back), no text, no logos.

---

## 2. Étape 1 — Choisir une image dans l'app
**Emplacement** : `index.html`, section `#comment-ca-marche`, premier `.step-card`
**Format recommandé** : 800×800px (carré)

> Clean modern mobile app screenshot mockup, UI for a phone case companion
> app called photo/design gallery picker. Shows a grid of thumbnail images
> (abstract art, photos, quotes, a weekly planner) ready to be selected,
> with a highlighted "Send to case" or upload button. Minimal, premium tech
> UI design, dark mode interface with indigo/purple accent color, displayed
> on a smartphone screen mockup, soft studio lighting, no real brand logos.

---

## 3. Étape 2 — Approcher le téléphone (transfert NFC)
**Emplacement** : `index.html`, section `#comment-ca-marche`, deuxième `.step-card`
**Format recommandé** : 800×800px (carré)

> Close-up product photo of a hand holding a smartphone, bringing its back
> close to the back of a matte black rugged phone case that has a built-in
> square e-ink display. A subtle glowing NFC/wireless transfer icon or
   soft light waves visible between the two devices, suggesting a wireless
> data transfer moment. Clean light gray background, soft studio lighting,
> realistic, premium tech product photography, no text overlays.

---

## 4. Étape 3 — Le design apparaît
**Emplacement** : `index.html`, section `#comment-ca-marche`, troisième `.step-card`
**Format recommandé** : 800×800px (carré)

> Product photo of the back of a matte black rugged phone case lying flat,
> its built-in square e-ink display now showing a finished abstract black
> brushstroke artwork with red and yellow accents, crisp and clearly
> visible, like a small e-reader screen. Top-down or slight angle view,
> soft studio lighting, light neutral background, premium tech product
> shot, high detail, no text, no logos.

---

---

## 5. Bannière comparative "Notre produit VS les autres" (style capture partagée)

⚠️ **Important avant de générer** : les IA de génération d'image (Midjourney,
DALL-E, Bing Image Creator...) rendent très mal le texte, et **encore plus
mal l'arabe** (lettres déformées, mots incorrects, sens de lecture cassé).
Les prompts ci-dessous génèrent donc **le visuel seul, sans aucun texte**
(`no text, no labels, no typography`). Le texte arabe (déjà traduit,
prêt à copier-coller) est fourni séparément à ajouter ensuite dans un outil
comme **Canva** ou **Figma**, où tu contrôles la police et l'exactitude du
texte à 100%.

### 5a. Bannière héro avec icônes de fonctionnalités
**Inspiration** : 1ère image partagée (portrait + icônes NFC/DIY Photo/écran 4 couleurs...)
**Format recommandé** : 1500×700px (paysage large)

> Professional lifestyle product photography, split composition. On one
> side, a young woman warmly holding a plush toy close to her face, soft
> natural smile, wearing a lavender hoodie, soft studio lighting, pastel
> light blue-gray background. On the other side, two smartphones: one
> showing a photo editor app interface with a portrait photo being cropped,
> and next to it the back of a matte black rugged phone case with a
> built-in square e-ink display showing that same portrait photo. Clean
> premium tech advertising style, soft pastel color palette, no text, no
> labels, no typography, empty space reserved on the left third of the
> image for a headline to be added later.

**Texte arabe à ajouter ensuite (zone titre, à gauche) :**
```
غطاء الحبر الذكي
```
**Texte arabe pour les 6 icônes (à recréer en dessous de chaque icône) :**
| Icône | Texte original | Texte arabe |
|---|---|---|
| NFC | NFC | NFC *(généralement gardé tel quel, terme technique universel)* |
| Photo | DIY Photo | صورة مخصصة |
| Écran couleur | Four-Color Screen | شاشة رباعية الألوان |
| Batterie barrée | No Battery | بدون استهلاك للبطارية |
| Bluetooth barré | No Bluetooth | بدون بلوتوث |
| 15S | 15s Screen Projection | عرض الصورة خلال ١٥ ثانية |

---

### 5b. Comparatif "Taille d'écran"
**Format recommandé** : 1500×650px (paysage)

> Split-screen comparison photo mockup, two smartphone cases side by side.
> Left case (on a light blue background) has a larger, brighter square
> e-ink display showing a crisp, colorful outdoor portrait photo of a woman
> with her dog in a lavender field. Right case (on a dark gray background)
> has a visibly smaller display showing the same photo but slightly grainy
> and duller. Clean premium tech comparison layout, empty rounded badge
> shape in the top-left reserved for a label, empty space above both cases
> reserved for a title, no text, no numbers, no typography.

**Texte arabe à ajouter ensuite :**
- Titre en haut : `منتجنا مقابل منتجات المنافسين`
- Badge : `حجم الشاشة`
- Sous l'écran de gauche (✓ vert) : `٤.٠ إنش – ٥٢٨×٧٦٨ – شاشة HD كبيرة بألوان أكثر ثراءً`
- Sous l'écran de droite (✗ rouge) : `٣.٧ إنش – ٢٤٠×٤١٦ – شاشة صغيرة ومليئة بالتشويش`

---

### 5c. Comparatif "Mode couleur"
**Format recommandé** : 1500×650px (paysage)

> Split-screen comparison photo mockup, two smartphone cases side by side.
> Left case (on a light blue background) shows a vivid, richly colored
> square e-ink display of a father lifting his laughing son on his
> shoulders outdoors. Right case (on a dark gray background) shows the same
> photo but visibly grainy, washed out, with muted dull colors and visible
> noise. Clean premium tech comparison layout, empty rounded badge shape in
> the top-left reserved for a label, empty space above reserved for a
> title, no text, no typography.

**Texte arabe à ajouter ensuite :**
- Titre en haut : `منتجنا مقابل منتجات المنافسين`
- Badge : `وضع الألوان`
- Sous l'écran de gauche (✓ vert) : `شاشة HD رباعية الألوان – ألوان أكثر ثراءً وتفاصيل أدق`
- Sous l'écran de droite (✗ rouge) : `شاشة ثلاثية الألوان بجودة صورة وألوان ضعيفة`

---

### 5d. Séquence "Comment ça marche" en 5 étapes (écrans d'app)

⚠️ Ce type de visuel (vraies captures d'écran d'interface avec boutons,
menus iOS, flèches de progression) est **très difficile à obtenir
correctement via une IA générative** — les IA inventent des interfaces qui
n'existent pas et le texte des boutons sera illisible ou faux. Pour ce
visuel précis, je recommande plutôt de **le construire dans Canva/Figma**
à partir de vraies captures d'écran de ton app (ou de mockups d'iPhone
gratuits), reliées par des flèches. Je peux te proposer une structure/mise
en page si tu veux, une fois que tu as les vraies captures.

Si tu veux quand même tenter l'IA pour une base visuelle générale
(silhouettes de téléphones + flèches, sans se soucier du contenu d'écran) :

> Five sequential smartphone mockups arranged in a horizontal row, connected
> by bold cyan arrow icons pointing right between each one. Each phone shows
> a blank white or light gray screen (to be filled in later). The final
> phone on the right is shown from the back, displaying a colorful square
> e-ink screen with a photo. Clean minimal tech infographic style, light
> background, no text, no UI details, no typography.

**Texte arabe des 5 étapes (à ajouter sous chaque téléphone) :**
1. `الخطوة ١: نزّل تطبيق Keefrash`
2. `الخطوة ٢: تأكد من تفعيل خاصية NFC في هاتفك`
3. `الخطوة ٣: اختر خلفية أو صورة خاصة بك`
4. `الخطوة ٤: اضغط على إرسال الصورة إلى الشاشة`
5. `الخطوة ٥: تم الإرسال بنجاح`

---

## Notes d'intégration

- Une fois les images générées, donne-moi le(s) chemin(s) des fichiers (ex.
  `D:\Images\hero.png`) — je les copierai dans `assets/images/` et
  remplacerai les placeholders correspondants dans `index.html`.
- Format conseillé : PNG ou JPG, poids raisonnable (< 500 Ko/image si
  possible) pour ne pas ralentir le site sur mobile/3G.
- Si un rendu ne te convainc pas, régénère-le plusieurs fois avec le même
  prompt : ces outils donnent des résultats différents à chaque tentative.

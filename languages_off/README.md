# Oxtilofastcal translations

Place your `.mo` and `.po` translation files here.

The plugin includes built-in Polish translations as a fallback.
If you want to use custom translations, create:

- `oxtilofastcal-pl_PL.po` - source translation file
- `oxtilofastcal-pl_PL.mo` - compiled translation file

## Generating .mo files

Use `msgfmt` or a tool like Poedit to compile `.po` files to `.mo` format.

```bash
msgfmt oxtilofastcal-pl_PL.po -o oxtilofastcal-pl_PL.mo
```

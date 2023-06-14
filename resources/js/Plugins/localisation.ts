import { App, Plugin } from 'vue';

export const trans = (key: string, replace: Record<string, string>, Translations: TranslationDictionary) => {
    const [category, name] = key.split('.');

    if (typeof name === 'undefined' && Translations.hasOwnProperty(category)) {
        if (typeof Translations[category] !== "string") return key;
        return checkForVariables(Translations[category] as string, replace);
    }

    if (!Translations.hasOwnProperty(category)) return key;
    if (typeof Translations[category] === "string") return key;
    // @ts-ignore
    return checkForVariables(Translations[category][name] as string, replace)
}

export const checkForVariables = (translation: string, replace: Record<string, string>) => {
    if (Object.keys(replace).length === 0) return translation

    return Object.keys(replace).reduce((carry, key) => {
        return carry.replace(':' + key, replace[key]);
    }, translation);
}

export const Localisation : Plugin = {
    install: (v: App, options: TranslationDictionary) => v.mixin({
        methods: {
            __: (key: string, replace: Record<string, string> = {}, config = options) => trans(key, replace, config),
            trans: (key: string, replace: Record<string, string> = {}, config = options) => trans(key, replace, config)
        }
    })
}
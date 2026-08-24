import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { messages } from './i18n';

export function useI18n() {
    const page = usePage();
    const locale = computed(() => page.props.locale ?? 'pt');

    // t('secao.chave') -> texto no idioma atual (com fallback pra PT e pra chave).
    const t = (chave) => {
        const buscar = (dic) => chave.split('.').reduce((o, k) => (o == null ? o : o[k]), dic);
        return buscar(messages[locale.value]) ?? buscar(messages.pt) ?? chave;
    };

    return { t, locale };
}

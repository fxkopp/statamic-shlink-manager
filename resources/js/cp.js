import Index from './pages/Index.vue';
import Create from './pages/Create.vue';
import Edit from './pages/Edit.vue';
import Show from './pages/Show.vue';
import RecentLinksWidget from './components/RecentLinksWidget.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('shlink-manager::Index', Index);
    Statamic.$inertia.register('shlink-manager::Create', Create);
    Statamic.$inertia.register('shlink-manager::Edit', Edit);
    Statamic.$inertia.register('shlink-manager::Show', Show);

    Statamic.$components.register('shlink-manager-recent-links-widget', RecentLinksWidget);
});

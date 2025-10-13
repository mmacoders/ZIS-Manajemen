import { AppPageProps } from '@/types/index';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
    
    // Re-export Vue functions to ensure proper typing
    export * from '@vue/runtime-core'
}

// Declare vue-highcharts component
declare module 'vue-highcharts' {
    import { DefineComponent } from 'vue';
    
    interface VueHighchartsOptions {
        Highcharts: any;
    }
    
    const createHighcharts: (name: string, Highcharts: any) => DefineComponent;
    const install: (app: any, options: VueHighchartsOptions) => void;
    
    export { createHighcharts };
    export default install;
}

// Declare global Chart component registered by vue-highcharts
declare const Chart: any;

// Highcharts module declarations
declare module 'highcharts/highcharts-more' {
    const HighchartsMore: (highcharts: any) => void;
    export default HighchartsMore;
}

declare module 'highcharts/modules/exporting' {
    const Exporting: (highcharts: any) => void;
    export default Exporting;
}
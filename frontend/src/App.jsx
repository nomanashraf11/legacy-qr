import { BrowserRouter } from "react-router-dom";
import { AppRoutes } from "./routes/AppRoutes";
import { Provider } from "react-redux";

import { store } from "./redux/store";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { TourProvider } from "@reactour/tour";

const queryClient = new QueryClient();

export default function App() {
  return (
    <BrowserRouter>
      <TourProvider showBadge={false} className="!text-neutral-900 rounded-lg">
        <Provider store={store}>
          <QueryClientProvider client={queryClient}>
            <AppRoutes />
          </QueryClientProvider>
        </Provider>
      </TourProvider>
    </BrowserRouter>
  );
}

import { useState, useMemo, useEffect } from "react";
import {
  Inbox,
  FileText,
  CreditCard,
  Package,
  Settings,
  ExternalLink,
  Search,
  Clock,
  CheckCircle2,
  Truck,
  AlertTriangle,
  Save,
  Send,
  ChevronRight,
  ArrowRight,
  RotateCcw,
} from "lucide-react";

// ─── Types ───────────────────────────────────────────────────────────────────

type Screen = "queue" | "quote" | "payment" | "tracking" | "settings";
type OrderStatus =
  | "new"
  | "quoted"
  | "payment_pending"
  | "payment_confirmed"
  | "ordered"
  | "shipped"
  | "delivered";

interface Order {
  id: string;
  customer: string;
  phone: string;
  link: string;
  submittedAt: Date;
  status: OrderStatus;
  amount?: number;
  paymentClaimedAt?: Date;
  trackingNote?: string;
}

// ─── Seed Data ────────────────────────────────────────────────────────────────

const now = new Date();
const minsAgo = (m: number) => new Date(now.getTime() - m * 60_000);

const INITIAL_ORDERS: Order[] = [
  {
    id: "SQ-2024-041",
    customer: "Faadumo Xasan",
    phone: "0634-112233",
    link: "https://www.shein.com/SHEIN-CURVE-Floral-Print-Midi-Dress-p-22943871.html",
    submittedAt: minsAgo(6),
    status: "new",
  },
  {
    id: "SQ-2024-040",
    customer: "Caisha Cabdi",
    phone: "0634-223344",
    link: "https://www.amazon.com/dp/B09XK8LMBZ/ref=sr_1_3_sspa?keywords=running+shoes",
    submittedAt: minsAgo(18),
    status: "new",
  },
  {
    id: "SQ-2024-039",
    customer: "Maxamed Warsame",
    phone: "0634-334455",
    link: "https://www.shein.com/Men-Letter-Graphic-Tee-p-18234567.html",
    submittedAt: minsAgo(31),
    status: "new",
  },
  {
    id: "SQ-2024-038",
    customer: "Hodan Ciise",
    phone: "0634-445566",
    link: "https://www.amazon.com/dp/B07XTQM3GZ/ref=cm_sw_r_tw_dp_x_abcdEFGHij",
    submittedAt: minsAgo(47),
    status: "new",
  },
  {
    id: "SQ-2024-037",
    customer: "Saciid Muuse",
    phone: "0634-556677",
    link: "https://www.shein.com/SHEIN-Kids-Girls-Rainbow-Stripe-Leggings-p-19876543.html",
    submittedAt: minsAgo(72),
    status: "new",
  },
  {
    id: "SQ-2024-036",
    customer: "Asad Jaamac",
    phone: "0634-667788",
    link: "https://www.amazon.com/dp/B08N5WRWNW/ref=mp_s_a_1_3?keywords=bluetooth+earbuds",
    submittedAt: minsAgo(95),
    status: "new",
  },
  {
    id: "SQ-2024-035",
    customer: "Nimo Dahir",
    phone: "0634-778899",
    link: "https://www.shein.com/SHEIN-Solid-Button-Up-Blouse-p-17654321.html",
    submittedAt: minsAgo(118),
    status: "new",
  },
  {
    id: "SQ-2024-034",
    customer: "Liibaan Cabdalle",
    phone: "0634-889900",
    link: "https://www.amazon.com/dp/B096KLBQ4P/ref=sr_1_1_sspa?keywords=yoga+mat",
    submittedAt: minsAgo(143),
    status: "new",
  },
  {
    id: "SQ-2024-033",
    customer: "Asha Guuleed",
    phone: "0634-001122",
    link: "https://www.shein.com/Women-V-Neck-Ruffle-Hem-Dress-p-16543210.html",
    submittedAt: minsAgo(201),
    status: "payment_pending",
    amount: 48.5,
    paymentClaimedAt: minsAgo(38),
  },
  {
    id: "SQ-2024-032",
    customer: "Cumar Farax",
    phone: "0634-112244",
    link: "https://www.amazon.com/dp/B07YFF3JKQ",
    submittedAt: minsAgo(280),
    status: "payment_pending",
    amount: 72.0,
    paymentClaimedAt: minsAgo(12),
  },
  {
    id: "SQ-2024-031",
    customer: "Shukri Badaar",
    phone: "0634-223355",
    link: "https://www.shein.com/Floral-Print-Wrap-Midi-Skirt-p-15432100.html",
    submittedAt: minsAgo(320),
    status: "payment_pending",
    amount: 31.75,
    paymentClaimedAt: minsAgo(55),
  },
  {
    id: "SQ-2024-030",
    customer: "Barwaaqo Ibraahim",
    phone: "0634-334466",
    link: "https://www.amazon.com/dp/B08CF3D7QR",
    submittedAt: minsAgo(400),
    status: "payment_pending",
    amount: 95.0,
    paymentClaimedAt: minsAgo(8),
  },
  {
    id: "SQ-2024-029",
    customer: "Deeqa Axmed",
    phone: "0634-445577",
    link: "https://www.shein.com/SHEIN-MOD-Colorblock-Hoodie-p-14321000.html",
    submittedAt: minsAgo(480),
    status: "payment_pending",
    amount: 39.25,
    paymentClaimedAt: minsAgo(41),
  },
  {
    id: "SQ-2024-028",
    customer: "Khadiijo Nuur",
    phone: "0634-556688",
    link: "https://www.amazon.com/dp/B07H5NVXBK",
    submittedAt: minsAgo(560),
    status: "payment_pending",
    amount: 58.0,
    paymentClaimedAt: minsAgo(22),
  },
  {
    id: "SQ-2024-027",
    customer: "Farhan Cali",
    phone: "0634-667799",
    link: "https://www.shein.com/Men-Slogan-Graphic-Oversized-Tee-p-13210000.html",
    submittedAt: minsAgo(700),
    status: "payment_confirmed",
    amount: 44.5,
  },
  {
    id: "SQ-2024-026",
    customer: "Layla Xirsi",
    phone: "0634-778800",
    link: "https://www.amazon.com/dp/B09G3HRMVK",
    submittedAt: minsAgo(900),
    status: "ordered",
    amount: 67.0,
    trackingNote: "Ordered from Shein — awaiting dispatch confirmation",
  },
  {
    id: "SQ-2024-025",
    customer: "Ramla Yuusuf",
    phone: "0634-889911",
    link: "https://www.shein.com/SHEIN-Elegant-Satin-Slip-Dress-p-12100000.html",
    submittedAt: minsAgo(1440),
    status: "shipped",
    amount: 53.25,
    trackingNote: "DHL tracking: 1ZW999AA0399152001",
  },
  {
    id: "SQ-2024-024",
    customer: "Cabdirashiid Soofe",
    phone: "0634-990022",
    link: "https://www.amazon.com/dp/B01N3ACVVV",
    submittedAt: minsAgo(2880),
    status: "delivered",
    amount: 88.0,
  },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatTimeAgo(date: Date): string {
  const diff = Math.floor((Date.now() - date.getTime()) / 60_000);
  if (diff < 60) return `${diff}m ago`;
  const h = Math.floor(diff / 60);
  if (h < 24) return `${h}h ${diff % 60}m ago`;
  return `${Math.floor(h / 24)}d ago`;
}

function truncateUrl(url: string, max = 52): string {
  try {
    const u = new URL(url);
    const path = u.hostname.replace("www.", "") + u.pathname;
    return path.length > max ? path.slice(0, max) + "…" : path;
  } catch {
    return url.length > max ? url.slice(0, max) + "…" : url;
  }
}

const STATUS_LABELS: Record<OrderStatus, string> = {
  new: "New",
  quoted: "Quoted",
  payment_pending: "Awaiting Payment",
  payment_confirmed: "Payment Confirmed",
  ordered: "Ordered",
  shipped: "Shipped",
  delivered: "Delivered",
};

const STATUS_COLORS: Record<OrderStatus, string> = {
  new: "bg-blue-50 text-blue-700",
  quoted: "bg-yellow-50 text-yellow-700",
  payment_pending: "bg-orange-50 text-orange-700",
  payment_confirmed: "bg-green-50 text-green-700",
  ordered: "bg-indigo-50 text-indigo-700",
  shipped: "bg-purple-50 text-purple-700",
  delivered: "bg-gray-100 text-gray-600",
};

// ─── Sub-components ───────────────────────────────────────────────────────────

function StatusBadge({ status }: { status: OrderStatus }) {
  return (
    <span
      className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${STATUS_COLORS[status]}`}
    >
      {STATUS_LABELS[status]}
    </span>
  );
}

interface TableProps {
  children: React.ReactNode;
}
function Table({ children }: TableProps) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm border-collapse">{children}</table>
    </div>
  );
}

// ─── Screen: Incoming Queue ───────────────────────────────────────────────────

function IncomingQueue({
  orders,
  onQuote,
}: {
  orders: Order[];
  onQuote: (order: Order) => void;
}) {
  const [search, setSearch] = useState("");
  const filtered = useMemo(
    () =>
      orders.filter(
        (o) =>
          o.customer.toLowerCase().includes(search.toLowerCase()) ||
          o.id.toLowerCase().includes(search.toLowerCase())
      ),
    [orders, search]
  );

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="flex items-center justify-between px-6 py-4 border-b border-border bg-card">
        <div>
          <h1 className="text-base font-semibold text-foreground">Incoming Queue</h1>
          <p className="text-xs text-muted-foreground mt-0.5">
            {orders.length} new orders awaiting quotes
          </p>
        </div>
        <div className="relative">
          <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
          <input
            type="text"
            placeholder="Search orders…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-8 pr-3 py-1.5 text-sm border border-border rounded-md bg-background focus:outline-none focus:ring-1 focus:ring-[#431475] w-56"
          />
        </div>
      </div>

      {/* Table */}
      <div className="flex-1 overflow-auto px-6 py-4">
        <div className="bg-card border border-border rounded-lg overflow-hidden">
          <Table>
            <thead>
              <tr className="bg-muted border-b border-border">
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-28">
                  Order ID
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-44">
                  Customer
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Submitted Link
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-32">
                  Submitted
                </th>
                <th className="px-4 py-2.5 w-24"></th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((order, i) => (
                <tr
                  key={order.id}
                  className={`border-b border-border last:border-0 hover:bg-muted/40 transition-colors ${
                    i % 2 === 0 ? "" : "bg-[#fafafa]"
                  }`}
                >
                  <td className="px-4 py-3">
                    <span className="font-mono text-xs text-muted-foreground">
                      {order.id}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <span className="font-medium text-foreground">{order.customer}</span>
                  </td>
                  <td className="px-4 py-3">
                    <a
                      href={order.link}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-[#431475] hover:underline text-sm"
                      title={order.link}
                    >
                      <span className="font-mono text-xs">{truncateUrl(order.link)}</span>
                      <ExternalLink className="w-3 h-3 flex-shrink-0" />
                    </a>
                  </td>
                  <td className="px-4 py-3 text-muted-foreground text-xs">
                    <span className="inline-flex items-center gap-1">
                      <Clock className="w-3 h-3" />
                      {formatTimeAgo(order.submittedAt)}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-right">
                    <button
                      onClick={() => onQuote(order)}
                      className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-[#431475] rounded-md hover:bg-[#5a1d99] transition-colors"
                    >
                      Quote
                      <ChevronRight className="w-3 h-3" />
                    </button>
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={5} className="px-4 py-10 text-center text-muted-foreground text-sm">
                    No orders match your search.
                  </td>
                </tr>
              )}
            </tbody>
          </Table>
        </div>
      </div>
    </div>
  );
}

// ─── Screen: Quote Builder ────────────────────────────────────────────────────

function QuoteBuilder({
  order,
  defaultFee,
  defaultShipping,
  onSent,
  onBack,
}: {
  order: Order | null;
  defaultFee: number;
  defaultShipping: number;
  onSent: (orderId: string) => void;
  onBack: () => void;
}) {
  const [itemCost, setItemCost] = useState("");
  const [feePct, setFeePct] = useState(String(defaultFee));
  const [shipping, setShipping] = useState(String(defaultShipping));
  const [sent, setSent] = useState(false);

  const itemNum = parseFloat(itemCost) || 0;
  const feeNum = parseFloat(feePct) || 0;
  const shipNum = parseFloat(shipping) || 0;
  const feeAmount = itemNum * (feeNum / 100);
  const total = itemNum + feeAmount + shipNum;

  function handleSend() {
    if (!order || total <= 0) return;
    setSent(true);
    setTimeout(() => {
      onSent(order.id);
      setSent(false);
    }, 1200);
  }

  if (!order) {
    return (
      <div className="flex flex-col h-full items-center justify-center text-muted-foreground gap-3">
        <FileText className="w-10 h-10 opacity-30" />
        <p className="text-sm">Select an order from the Incoming Queue to build a quote.</p>
        <button
          onClick={onBack}
          className="mt-2 text-sm text-[#431475] underline hover:no-underline"
        >
          Go to Incoming Queue
        </button>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="flex items-center gap-3 px-6 py-4 border-b border-border bg-card">
        <button
          onClick={onBack}
          className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1 transition-colors"
        >
          <RotateCcw className="w-3 h-3" />
          Back
        </button>
        <span className="text-muted-foreground">/</span>
        <h1 className="text-base font-semibold text-foreground">Quote Builder</h1>
        <span className="ml-auto font-mono text-xs text-muted-foreground">{order.id}</span>
      </div>

      <div className="flex-1 overflow-auto px-6 py-6 flex gap-6">
        {/* Left: customer link */}
        <div className="flex-1 max-w-xl">
          {/* Order link */}
          <div className="bg-card border border-border rounded-lg p-5 mb-4">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-2">
              Customer Submission
            </p>
            <p className="font-semibold text-foreground mb-1">{order.customer}</p>
            <a
              href={order.link}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 text-[#431475] hover:underline break-all text-sm"
            >
              <ExternalLink className="w-4 h-4 flex-shrink-0" />
              <span>{order.link}</span>
            </a>
            <p className="mt-3 text-xs text-muted-foreground">
              Submitted {formatTimeAgo(order.submittedAt)}
            </p>
          </div>

          {/* Product preview hint */}
          <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
            <AlertTriangle className="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
            <p className="text-xs text-amber-800">
              Always open the link before quoting to verify availability, size options, and current
              price. Prices may differ from when the customer submitted.
            </p>
          </div>
        </div>

        {/* Right: Quote form */}
        <div className="w-80 flex-shrink-0">
          <div className="bg-card border border-border rounded-lg p-5">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-4">
              Build Quote
            </p>

            <div className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-foreground mb-1">
                  Item Cost (USD)
                </label>
                <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                    $
                  </span>
                  <input
                    type="number"
                    min="0"
                    step="0.01"
                    value={itemCost}
                    onChange={(e) => setItemCost(e.target.value)}
                    placeholder="0.00"
                    className="w-full pl-7 pr-3 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475]"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-foreground mb-1">
                  Service Fee (%)
                </label>
                <div className="relative">
                  <input
                    type="number"
                    min="0"
                    step="0.5"
                    value={feePct}
                    onChange={(e) => setFeePct(e.target.value)}
                    className="w-full pl-3 pr-8 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475]"
                  />
                  <span className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                    %
                  </span>
                </div>
                {itemNum > 0 && (
                  <p className="text-xs text-muted-foreground mt-1">
                    = ${feeAmount.toFixed(2)} on ${itemNum.toFixed(2)}
                  </p>
                )}
              </div>

              <div>
                <label className="block text-xs font-medium text-foreground mb-1">
                  Shipping & Customs (USD)
                </label>
                <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                    $
                  </span>
                  <input
                    type="number"
                    min="0"
                    step="0.5"
                    value={shipping}
                    onChange={(e) => setShipping(e.target.value)}
                    className="w-full pl-7 pr-3 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475]"
                  />
                </div>
              </div>
            </div>

            {/* Total */}
            <div className="mt-5 pt-4 border-t border-border">
              <div className="flex items-baseline justify-between mb-1">
                <span className="text-xs text-muted-foreground">Item cost</span>
                <span className="text-sm font-mono">${itemNum.toFixed(2)}</span>
              </div>
              <div className="flex items-baseline justify-between mb-1">
                <span className="text-xs text-muted-foreground">
                  Service fee ({feePct}%)
                </span>
                <span className="text-sm font-mono">${feeAmount.toFixed(2)}</span>
              </div>
              <div className="flex items-baseline justify-between mb-4">
                <span className="text-xs text-muted-foreground">Shipping</span>
                <span className="text-sm font-mono">${shipNum.toFixed(2)}</span>
              </div>
              <div className="flex items-center justify-between bg-[#431475] text-white rounded-lg px-4 py-3">
                <span className="text-sm font-semibold">Total</span>
                <span className="text-2xl font-bold font-mono">${total.toFixed(2)}</span>
              </div>
            </div>

            <button
              onClick={handleSend}
              disabled={total <= 0 || sent}
              className={`mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-md text-sm font-semibold transition-all ${
                total <= 0
                  ? "bg-muted text-muted-foreground cursor-not-allowed"
                  : sent
                  ? "bg-green-600 text-white"
                  : "bg-[#431475] text-white hover:bg-[#5a1d99]"
              }`}
            >
              {sent ? (
                <>
                  <CheckCircle2 className="w-4 h-4" />
                  Quote Sent!
                </>
              ) : (
                <>
                  <Send className="w-4 h-4" />
                  Send quote to customer
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Screen: Payment Confirmation ─────────────────────────────────────────────

function PaymentConfirmation({
  orders,
  onConfirm,
}: {
  orders: Order[];
  onConfirm: (orderId: string) => void;
}) {
  const [search, setSearch] = useState("");
  const filtered = useMemo(
    () =>
      orders.filter(
        (o) =>
          o.customer.toLowerCase().includes(search.toLowerCase()) ||
          o.id.toLowerCase().includes(search.toLowerCase()) ||
          (o.phone || "").includes(search)
      ),
    [orders, search]
  );

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between px-6 py-4 border-b border-border bg-card">
        <div>
          <h1 className="text-base font-semibold text-foreground">Payment Confirmation</h1>
          <p className="text-xs text-muted-foreground mt-0.5">
            {orders.length} orders awaiting payment verification
          </p>
        </div>
        <div className="relative">
          <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
          <input
            type="text"
            placeholder="Search orders…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-8 pr-3 py-1.5 text-sm border border-border rounded-md bg-background focus:outline-none focus:ring-1 focus:ring-[#431475] w-56"
          />
        </div>
      </div>

      <div className="flex-1 overflow-auto px-6 py-4">
        {/* Urgent notice */}
        <div className="mb-3 flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
          <AlertTriangle className="w-3.5 h-3.5 flex-shrink-0" />
          Rows highlighted in amber have been waiting 30+ minutes since payment was claimed.
        </div>

        <div className="bg-card border border-border rounded-lg overflow-hidden">
          <Table>
            <thead>
              <tr className="bg-muted border-b border-border">
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-28">
                  Order ID
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-40">
                  Customer
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-36">
                  Phone
                </th>
                <th className="text-right px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-32">
                  Expected (USD)
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-36">
                  Claimed
                </th>
                <th className="px-4 py-2.5 w-36"></th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((order) => {
                const claimedMins = order.paymentClaimedAt
                  ? Math.floor((Date.now() - order.paymentClaimedAt.getTime()) / 60_000)
                  : 0;
                const isUrgent = claimedMins >= 30;
                return (
                  <tr
                    key={order.id}
                    className={`border-b border-border last:border-0 transition-colors ${
                      isUrgent
                        ? "bg-amber-50 hover:bg-amber-100 border-l-2 border-l-amber-400"
                        : "hover:bg-muted/40"
                    }`}
                  >
                    <td className="px-4 py-3">
                      <span className="font-mono text-xs text-muted-foreground">{order.id}</span>
                    </td>
                    <td className="px-4 py-3 font-medium text-foreground">{order.customer}</td>
                    <td className="px-4 py-3 font-mono text-xs text-foreground">{order.phone}</td>
                    <td className="px-4 py-3 text-right">
                      <span className="font-semibold text-[#431475] font-mono">
                        ${order.amount?.toFixed(2)}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-xs text-muted-foreground">
                      <span className={`inline-flex items-center gap-1 ${isUrgent ? "text-amber-700 font-medium" : ""}`}>
                        <Clock className="w-3 h-3" />
                        {order.paymentClaimedAt ? formatTimeAgo(order.paymentClaimedAt) : "—"}
                        {isUrgent && (
                          <AlertTriangle className="w-3 h-3 text-amber-600" />
                        )}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <button
                        onClick={() => onConfirm(order.id)}
                        className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-[#431475] rounded-md hover:bg-[#5a1d99] transition-colors"
                      >
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        Confirm payment
                      </button>
                    </td>
                  </tr>
                );
              })}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-4 py-10 text-center text-muted-foreground text-sm">
                    No orders awaiting payment confirmation.
                  </td>
                </tr>
              )}
            </tbody>
          </Table>
        </div>
      </div>
    </div>
  );
}

// ─── Screen: Order Tracking ───────────────────────────────────────────────────

const NEXT_STATUS: Partial<Record<OrderStatus, OrderStatus>> = {
  payment_confirmed: "ordered",
  ordered: "shipped",
  shipped: "delivered",
};

const NEXT_LABEL: Partial<Record<OrderStatus, string>> = {
  payment_confirmed: "Mark as ordered",
  ordered: "Mark as shipped",
  shipped: "Mark as delivered",
};

function OrderTracking({
  orders,
  onAdvance,
  onNoteChange,
}: {
  orders: Order[];
  onAdvance: (orderId: string, nextStatus: OrderStatus) => void;
  onNoteChange: (orderId: string, note: string) => void;
}) {
  const [search, setSearch] = useState("");
  const [editingNote, setEditingNote] = useState<string | null>(null);
  const [noteValues, setNoteValues] = useState<Record<string, string>>({});

  const filtered = useMemo(
    () =>
      orders.filter(
        (o) =>
          o.customer.toLowerCase().includes(search.toLowerCase()) ||
          o.id.toLowerCase().includes(search.toLowerCase())
      ),
    [orders, search]
  );

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between px-6 py-4 border-b border-border bg-card">
        <div>
          <h1 className="text-base font-semibold text-foreground">Order Tracking</h1>
          <p className="text-xs text-muted-foreground mt-0.5">
            {orders.length} active orders in fulfillment
          </p>
        </div>
        <div className="relative">
          <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
          <input
            type="text"
            placeholder="Search orders…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-8 pr-3 py-1.5 text-sm border border-border rounded-md bg-background focus:outline-none focus:ring-1 focus:ring-[#431475] w-56"
          />
        </div>
      </div>

      <div className="flex-1 overflow-auto px-6 py-4">
        <div className="bg-card border border-border rounded-lg overflow-hidden">
          <Table>
            <thead>
              <tr className="bg-muted border-b border-border">
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-28">
                  Order ID
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-40">
                  Customer
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-36">
                  Status
                </th>
                <th className="text-right px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide w-28">
                  Amount
                </th>
                <th className="text-left px-4 py-2.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Tracking note
                </th>
                <th className="px-4 py-2.5 w-40"></th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((order) => {
                const next = NEXT_STATUS[order.status];
                const noteVal =
                  noteValues[order.id] !== undefined ? noteValues[order.id] : order.trackingNote ?? "";
                return (
                  <tr
                    key={order.id}
                    className="border-b border-border last:border-0 hover:bg-muted/40 transition-colors"
                  >
                    <td className="px-4 py-3">
                      <span className="font-mono text-xs text-muted-foreground">{order.id}</span>
                    </td>
                    <td className="px-4 py-3 font-medium text-foreground">{order.customer}</td>
                    <td className="px-4 py-3">
                      <StatusBadge status={order.status} />
                    </td>
                    <td className="px-4 py-3 text-right font-mono text-sm font-semibold text-[#431475]">
                      ${order.amount?.toFixed(2)}
                    </td>
                    <td className="px-4 py-3">
                      {editingNote === order.id ? (
                        <div className="flex items-center gap-2">
                          <input
                            autoFocus
                            type="text"
                            value={noteVal}
                            onChange={(e) =>
                              setNoteValues((v) => ({ ...v, [order.id]: e.target.value }))
                            }
                            onBlur={() => {
                              onNoteChange(order.id, noteVal);
                              setEditingNote(null);
                            }}
                            onKeyDown={(e) => {
                              if (e.key === "Enter") {
                                onNoteChange(order.id, noteVal);
                                setEditingNote(null);
                              }
                              if (e.key === "Escape") setEditingNote(null);
                            }}
                            className="flex-1 px-2 py-1 text-xs border border-[#431475] rounded focus:outline-none"
                            placeholder="Add tracking note…"
                          />
                        </div>
                      ) : (
                        <button
                          onClick={() => setEditingNote(order.id)}
                          className="text-xs text-muted-foreground hover:text-foreground text-left truncate max-w-xs block"
                          title="Click to edit note"
                        >
                          {noteVal || <span className="italic opacity-50">Add note…</span>}
                        </button>
                      )}
                    </td>
                    <td className="px-4 py-3 text-right">
                      {next && order.status !== "delivered" ? (
                        <button
                          onClick={() => onAdvance(order.id, next)}
                          className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-[#431475] rounded-md hover:bg-[#5a1d99] transition-colors whitespace-nowrap"
                        >
                          <ArrowRight className="w-3 h-3" />
                          {NEXT_LABEL[order.status]}
                        </button>
                      ) : (
                        <span className="inline-flex items-center gap-1 text-xs text-green-700 font-medium">
                          <CheckCircle2 className="w-3.5 h-3.5" />
                          Delivered
                        </span>
                      )}
                    </td>
                  </tr>
                );
              })}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-4 py-10 text-center text-muted-foreground text-sm">
                    No orders in tracking.
                  </td>
                </tr>
              )}
            </tbody>
          </Table>
        </div>
      </div>
    </div>
  );
}

// ─── Screen: Settings ─────────────────────────────────────────────────────────

interface SettingsData {
  defaultFeePct: number;
  defaultShipping: number;
  zaadNumber: string;
  edahabNumber: string;
}

function SettingsScreen({
  settings,
  onSave,
}: {
  settings: SettingsData;
  onSave: (s: SettingsData) => void;
}) {
  const [form, setForm] = useState({ ...settings });
  const [saved, setSaved] = useState(false);

  function handleSave() {
    onSave(form);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  return (
    <div className="flex flex-col h-full">
      <div className="px-6 py-4 border-b border-border bg-card">
        <h1 className="text-base font-semibold text-foreground">Settings</h1>
        <p className="text-xs text-muted-foreground mt-0.5">
          Global defaults for quotes and payment collection
        </p>
      </div>

      <div className="flex-1 overflow-auto px-6 py-6">
        <div className="max-w-lg space-y-6">
          {/* Pricing defaults */}
          <div className="bg-card border border-border rounded-lg p-5">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-4">
              Pricing Defaults
            </p>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-1">
                  Default Service Fee (%)
                </label>
                <p className="text-xs text-muted-foreground mb-2">
                  Pre-filled in the Quote Builder. Overridable per order.
                </p>
                <div className="relative w-40">
                  <input
                    type="number"
                    min="0"
                    step="0.5"
                    value={form.defaultFeePct}
                    onChange={(e) =>
                      setForm((f) => ({ ...f, defaultFeePct: parseFloat(e.target.value) || 0 }))
                    }
                    className="w-full pl-3 pr-8 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475]"
                  />
                  <span className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                    %
                  </span>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-1">
                  Default Shipping & Customs (USD)
                </label>
                <p className="text-xs text-muted-foreground mb-2">
                  Pre-filled in the Quote Builder. Overridable per order.
                </p>
                <div className="relative w-40">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                    $
                  </span>
                  <input
                    type="number"
                    min="0"
                    step="0.5"
                    value={form.defaultShipping}
                    onChange={(e) =>
                      setForm((f) => ({
                        ...f,
                        defaultShipping: parseFloat(e.target.value) || 0,
                      }))
                    }
                    className="w-full pl-7 pr-3 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475]"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Payment numbers */}
          <div className="bg-card border border-border rounded-lg p-5">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-4">
              Merchant Payment Numbers
            </p>
            <p className="text-xs text-muted-foreground mb-4">
              These numbers are shared with customers when requesting payment.
            </p>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-1">
                  ZAAD Number
                </label>
                <input
                  type="tel"
                  value={form.zaadNumber}
                  onChange={(e) => setForm((f) => ({ ...f, zaadNumber: e.target.value }))}
                  placeholder="e.g. 0634-000000"
                  className="w-full px-3 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475] font-mono"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-foreground mb-1">
                  eDahab Number
                </label>
                <input
                  type="tel"
                  value={form.edahabNumber}
                  onChange={(e) => setForm((f) => ({ ...f, edahabNumber: e.target.value }))}
                  placeholder="e.g. 0634-000000"
                  className="w-full px-3 py-2 text-sm border border-border rounded-md bg-input-background focus:outline-none focus:ring-1 focus:ring-[#431475] font-mono"
                />
              </div>
            </div>
          </div>

          <button
            onClick={handleSave}
            className={`flex items-center gap-2 px-5 py-2.5 rounded-md text-sm font-semibold transition-all ${
              saved
                ? "bg-green-600 text-white"
                : "bg-[#431475] text-white hover:bg-[#5a1d99]"
            }`}
          >
            {saved ? (
              <>
                <CheckCircle2 className="w-4 h-4" />
                Saved!
              </>
            ) : (
              <>
                <Save className="w-4 h-4" />
                Save settings
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Nav item ─────────────────────────────────────────────────────────────────

const NAV_ITEMS: { id: Screen; label: string; icon: React.ElementType; badge?: string }[] = [
  { id: "queue", label: "Incoming Queue", icon: Inbox },
  { id: "quote", label: "Quote Builder", icon: FileText },
  { id: "payment", label: "Payment Confirmation", icon: CreditCard },
  { id: "tracking", label: "Order Tracking", icon: Package },
  { id: "settings", label: "Settings", icon: Settings },
];

// ─── Root App ─────────────────────────────────────────────────────────────────

export default function App() {
  const [screen, setScreen] = useState<Screen>("queue");
  const [orders, setOrders] = useState<Order[]>(INITIAL_ORDERS);
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [appSettings, setAppSettings] = useState<SettingsData>({
    defaultFeePct: 12,
    defaultShipping: 8,
    zaadNumber: "0634-777888",
    edahabNumber: "0634-999000",
  });

  // Derived order lists
  const queueOrders = orders.filter((o) => o.status === "new");
  const paymentOrders = orders.filter((o) => o.status === "payment_pending");
  const trackingOrders = orders.filter((o) =>
    ["payment_confirmed", "ordered", "shipped", "delivered"].includes(o.status)
  );

  // Badge counts
  const badges: Partial<Record<Screen, number>> = {
    queue: queueOrders.length,
    payment: paymentOrders.length,
  };

  function handleQuote(order: Order) {
    setSelectedOrder(order);
    setScreen("quote");
  }

  function handleQuoteSent(orderId: string) {
    setOrders((prev) =>
      prev.map((o) =>
        o.id === orderId
          ? { ...o, status: "payment_pending", paymentClaimedAt: undefined }
          : o
      )
    );
    setScreen("payment");
  }

  function handleConfirmPayment(orderId: string) {
    setOrders((prev) =>
      prev.map((o) =>
        o.id === orderId ? { ...o, status: "payment_confirmed" } : o
      )
    );
  }

  function handleAdvance(orderId: string, nextStatus: OrderStatus) {
    setOrders((prev) =>
      prev.map((o) => (o.id === orderId ? { ...o, status: nextStatus } : o))
    );
  }

  function handleNoteChange(orderId: string, note: string) {
    setOrders((prev) =>
      prev.map((o) => (o.id === orderId ? { ...o, trackingNote: note } : o))
    );
  }

  function navTo(id: Screen) {
    setScreen(id);
    if (id !== "quote") setSelectedOrder(null);
  }

  return (
    <div
      className="flex h-screen overflow-hidden"
      style={{ fontFamily: "'Inter', system-ui, sans-serif" }}
    >
      {/* ── Sidebar ─────────────────────────────────────────────────────────── */}
      <aside className="w-56 flex-shrink-0 flex flex-col" style={{ background: "#431475" }}>
        {/* Logo */}
        <div className="px-5 py-5 border-b border-white/10">
          <div className="flex items-center gap-2.5">
            {/* S mark */}
            <div className="w-8 h-8 rounded-md bg-white/15 flex items-center justify-center flex-shrink-0">
              <span
                style={{
                  fontFamily: "'Inter', sans-serif",
                  fontWeight: 700,
                  fontSize: 18,
                  color: "white",
                  letterSpacing: "-0.5px",
                }}
              >
                S
              </span>
            </div>
            <div>
              <p className="text-white font-bold text-sm tracking-widest uppercase">Suuqsade</p>
              <p className="text-white/50 text-[10px] font-medium tracking-wide uppercase">Admin</p>
            </div>
          </div>
        </div>

        {/* Nav */}
        <nav className="flex-1 py-3 overflow-y-auto">
          {NAV_ITEMS.map(({ id, label, icon: Icon }) => {
            const active = screen === id;
            const badge = badges[id];
            return (
              <button
                key={id}
                onClick={() => navTo(id)}
                className={`w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-all relative ${
                  active
                    ? "text-white bg-white/12"
                    : "text-white/60 hover:text-white hover:bg-white/6"
                }`}
              >
                {active && (
                  <span
                    className="absolute left-0 top-1 bottom-1 w-0.5 rounded-r-full bg-white"
                    aria-hidden
                  />
                )}
                <Icon className={`w-4 h-4 flex-shrink-0 ${active ? "opacity-100" : "opacity-70"}`} />
                <span className="flex-1 text-left leading-none">{label}</span>
                {badge != null && badge > 0 && (
                  <span
                    className={`text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center ${
                      active ? "bg-white text-[#431475]" : "bg-white/20 text-white"
                    }`}
                  >
                    {badge}
                  </span>
                )}
              </button>
            );
          })}
        </nav>

        {/* Footer */}
        <div className="px-5 py-4 border-t border-white/10">
          <p className="text-white/30 text-[10px] uppercase tracking-wide">
            Internal Tool · v1.0
          </p>
        </div>
      </aside>

      {/* ── Main content ────────────────────────────────────────────────────── */}
      <main className="flex-1 flex flex-col overflow-hidden bg-background">
        {screen === "queue" && (
          <IncomingQueue orders={queueOrders} onQuote={handleQuote} />
        )}
        {screen === "quote" && (
          <QuoteBuilder
            order={selectedOrder}
            defaultFee={appSettings.defaultFeePct}
            defaultShipping={appSettings.defaultShipping}
            onSent={handleQuoteSent}
            onBack={() => setScreen("queue")}
          />
        )}
        {screen === "payment" && (
          <PaymentConfirmation orders={paymentOrders} onConfirm={handleConfirmPayment} />
        )}
        {screen === "tracking" && (
          <OrderTracking
            orders={trackingOrders}
            onAdvance={handleAdvance}
            onNoteChange={handleNoteChange}
          />
        )}
        {screen === "settings" && (
          <SettingsScreen
            settings={appSettings}
            onSave={(s) => setAppSettings(s)}
          />
        )}
      </main>
    </div>
  );
}

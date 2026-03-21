@extends('layouts.doctor')

@section('title', 'Subscription Plans')

@section('content')
<div style="max-width:960px; margin:0 auto; padding:40px 20px;">

    <h1 style="font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:600; color:var(--ink); margin-bottom:8px;">
        Subscription Plans
    </h1>
    <p style="color:var(--txt-md); margin-bottom:40px;">Choose a plan that fits your practice</p>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px,1fr)); gap:24px;">

        {{-- Free Plan --}}
        <div style="background:var(--cream); border:2px solid var(--warm-bd); border-radius:16px; padding:32px 24px; text-align:center;">
            <div style="font-size:.85rem; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--txt-md); margin-bottom:12px;">Free</div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:2.5rem; font-weight:700; color:var(--ink);">₹0</div>
            <div style="color:var(--txt-md); font-size:.9rem; margin-bottom:24px;">per month</div>
            <ul style="text-align:left; list-style:none; padding:0; margin-bottom:32px; font-size:.9rem; color:var(--txt);">
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Up to 20 appointments/month</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Basic medical records</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Prescriptions</li>
                <li style="padding:8px 0; color:var(--txt-lt);">✗ Analytics dashboard</li>
                <li style="padding:8px 0; color:var(--txt-lt);">✗ Priority support</li>
            </ul>
            <div style="padding:10px 24px; border-radius:8px; background:var(--parch); color:var(--txt-md); font-weight:500; font-size:.9rem;">
                Current Plan
            </div>
        </div>

        {{-- Pro Plan --}}
        <div style="background:var(--cream); border:2px solid var(--leaf); border-radius:16px; padding:32px 24px; text-align:center; position:relative; box-shadow:0 4px 24px rgba(61,122,110,.15);">
            <div style="position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:var(--leaf); color:#fff; padding:4px 16px; border-radius:20px; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                Popular
            </div>
            <div style="font-size:.85rem; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--leaf); margin-bottom:12px;">Pro</div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:2.5rem; font-weight:700; color:var(--ink);">₹999</div>
            <div style="color:var(--txt-md); font-size:.9rem; margin-bottom:24px;">per month</div>
            <ul style="text-align:left; list-style:none; padding:0; margin-bottom:32px; font-size:.9rem; color:var(--txt);">
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Unlimited appointments</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Full medical records</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Prescriptions + PDF export</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Analytics dashboard</li>
                <li style="padding:8px 0;">✗ Priority support</li>
            </ul>
            <button style="width:100%; padding:12px 24px; border-radius:8px; background:var(--leaf); color:#fff; font-weight:600; font-size:.9rem; border:none; cursor:pointer;">
                Coming Soon
            </button>
        </div>

        {{-- Enterprise Plan --}}
        <div style="background:var(--cream); border:2px solid var(--warm-bd); border-radius:16px; padding:32px 24px; text-align:center;">
            <div style="font-size:.85rem; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--txt-md); margin-bottom:12px;">Enterprise</div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:2.5rem; font-weight:700; color:var(--ink);">₹2,499</div>
            <div style="color:var(--txt-md); font-size:.9rem; margin-bottom:24px;">per month</div>
            <ul style="text-align:left; list-style:none; padding:0; margin-bottom:32px; font-size:.9rem; color:var(--txt);">
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Everything in Pro</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Multi-clinic support</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Staff accounts</li>
                <li style="padding:8px 0; border-bottom:1px solid var(--warm-bd);">✓ Advanced analytics</li>
                <li style="padding:8px 0;">✓ Priority support</li>
            </ul>
            <button style="width:100%; padding:12px 24px; border-radius:8px; background:var(--ink); color:#fff; font-weight:600; font-size:.9rem; border:none; cursor:pointer;">
                Coming Soon
            </button>
        </div>

    </div>

    <div style="text-align:center; margin-top:40px; padding:24px; background:var(--cream); border-radius:12px; border:1px solid var(--warm-bd);">
        <p style="color:var(--txt-md); font-size:.9rem;">
            Subscription payments will be available soon. Stay tuned for updates!
        </p>
    </div>

</div>
@endsection

var simplemaps_usmap_mapdata={
  main_settings: {
    //General settings
		width: "responsive", //or 'responsive'
    background_color: "#FFFFFF",
    background_transparent: "yes",
    popups: "detect",
    
		//State defaults
		state_description: "",
    state_color: "#9d1925",
    state_hover_color: "#414042",
    state_url: "",
    border_size: 1,
    border_color: "#ffffff",
    all_states_inactive: "no",
    all_states_zoomable: "no",
    
		//Location defaults
		location_description: "Location description",
    location_color: "#FF0067",
    location_opacity: 0.8,
    location_hover_opacity: 1,
    location_url: "",
    location_size: 25,
    location_type: "square",
    location_border_color: "#FFFFFF",
    location_border: 2,
    location_hover_border: 2.5,
    all_locations_inactive: "yes",
    all_locations_hidden: "yes",
    
		//Label defaults
		label_color: "#ffffff",
    label_hover_color: "#ffffff",
    label_size: 16,
    label_font: "Arial",
    hide_labels: "no",
   
		//Zoom settings
		manual_zoom: "no",
    back_image: "no",
    arrow_box: "no",
    navigation_size: "40",
    navigation_color: "#f7f7f7",
    navigation_border_color: "#636363",
    initial_back: "no",
    initial_zoom: -1,
    initial_zoom_solo: "no",
    region_opacity: 1,
    region_hover_opacity: 0.6,
    zoom_out_incrementally: "yes",
    zoom_percentage: 0.99,
    zoom_time: 0.5,
    
		//Popup settings
		popup_color: "white",
    popup_opacity: 0.9,
    popup_shadow: 1,
    popup_corners: 5,
    popup_font: "12px/1.5 Verdana, Arial, Helvetica, sans-serif",
    popup_nocss: "no",
    
		//Advanced settings
		div: "amb_state_map",
    auto_load: "yes",
    rotate: "0",
    url_new_tab: "yes",
    images_directory: "default",
    import_labels: "no",
    fade_time: 0.1,
    link_text: "View Website"
  },
  state_specific: {
    AL: { name: "Alabama", url: amburl+"/alabama-metal-buildings", inactive: "no" },
    AZ: { name: "Arizona", url: amburl+"/arizona-metal-buildings", inactive: "no" },
    AR: { name: "Arkansas", url: amburl+"/arkansas-metal-buildings", inactive: "no" },
    CA: { name: "California", url: amburl+"/california-metal-buildings", inactive: "no" },
    CO: { name: "Colorado", url: amburl+"/colorado-metal-buildings", inactive: "no" }, 
    CT: { name: "Connecticut", url: amburl+"/connecticut-metal-buildings", inactive: "no" },
    DE: { name: "Delaware", url: amburl+"/delaware-metal-buildings", inactive: "no" },
    FL: { name: "Florida", url: amburl+"/florida-metal-buildings", inactive: "no" },
    GA: { name: "Georgia", url: amburl+"/georgia-metal-buildings", inactive: "no" },
    IL: { name: "Illinois", url: amburl+"/illinois-metal-buildings", inactive: "no" },
    IN: { name: "Indiana", url: amburl+"/indiana-metal-buildings", inactive: "no" },
    IA: { name: "Iowa", url: amburl+"/iowa-metal-buildings", inactive: "no" },
    KS: { name: "Kansas", url: amburl+"/kansas-metal-buildings", inactive: "no" },
    KY: { name: "Kentucky", url: amburl+"/kentucky-metal-buildings", inactive: "no" },
    LA: { name: "Louisiana", url: amburl+"/louisiana-metal-buildings", inactive: "no" },
    ME: { name: "Maine", url: amburl+"/maine-metal-buildings", inactive: "no" },
    MD: { name: "Maryland", url: amburl+"/maryland-metal-buildings", inactive: "no" },
    MA: { name: "Massachusetts", url: amburl+"/massachusetts-metal-buildings", inactive: "no" },
    MI: { name: "Michigan", url: amburl+"/michigan-metal-buildings", inactive: "no" },
    MN: { name: "Minnesota", url: amburl+"/minnesota-metal-buildings", inactive: "no" },
    MS: { name: "Mississippi", url: amburl+"/mississippi-metal-buildings", inactive: "no" },
    MO: { name: "Missouri", url: amburl+"/missouri-metal-buildings", inactive: "no" },
    NV: { name: "Nevada", url: amburl+"/nevada-metal-buildings", inactive: "no" },
    NH: { name: "New Hampshire", url: amburl+"/new-hampshire-metal-buildings", inactive: "no" },
    NJ: { name: "New Jersey", url: amburl+"/new-jersey-metal-buildings", inactive: "no" },
    NM: { name: "New Mexico", url: amburl+"/new-mexico-metal-buildings", inactive: "no" },
    NY: { name: "New York", url: amburl+"/new-york-metal-buildings", inactive: "no" },
    NC: { name: "North Carolina", url: amburl+"/north-carolina-metal-buildings", inactive: "no" },
    OH: { name: "Ohio", url: amburl+"/ohio-metal-buildings", inactive: "no" },
    OK: { name: "Oklahoma", url: amburl+"/oklahoma-metal-buildings", inactive: "no" },
    OR: { name: "Oregon", url: amburl+"/oregon-metal-buildings", inactive: "no" },
    PA: { name: "Pennsylvania", url: amburl+"/pennsylvania-metal-buildings", inactive: "no" },
    SC: { name: "South Carolina", url: amburl+"/south-carolina-metal-buildings", inactive: "no" },
    TN: { name: "Tennessee", url: amburl+"/tennessee-metal-buildings", inactive: "no" },
    TX: { name: "Texas", url: amburl+"/texas-metal-buildings", inactive: "no" },
    UT: { name: "Utah", url: amburl+"/utah-metal-buildings", inactive: "no" },
    VT: { name: "Vermont", url: amburl+"/vermont-metal-buildings", inactive: "no" },
    VA: { name: "Virginia", url: amburl+"/virginia-metal-buildings", inactive: "no" },
    WA: { name: "Washington", url: amburl+"/washington-metal-buildings", inactive: "no" },
    WV: { name: "West Virginia", url: amburl+"/west-virginia-metal-buildings", inactive: "no" },
    WI: { name: "Wisconsin", url: amburl+"/wisconsin-metal-buildings", inactive: "no" },

    ID: { name: "Idaho", url: "default", inactive: "yes" },
    MT: { name: "Montana", url: "default", inactive: "yes" },
    NE: { name: "Nebraska", url: "default", inactive: "yes" },
    ND: { name: "North Dakota", url: "default", inactive: "yes" },
    RI: { name: "Rhode Island", url: "default", inactive: "yes" },
    SD: { name: "South Dakota", url: "default", inactive: "yes" },
    WY: { name: "Wyoming", url: "default", inactive: "yes" },
    GU: { name: "Guam", url: "default", inactive: "yes", hide:"yes" },
    AK: { name: "Alaska", url: "default", inactive: "yes", hide:"yes" },
    HI: { name: "Hawaii", url: "default", inactive: "yes", hide:"yes" },
    DC: { name: "District of Columbia", url: "default", inactive: "yes" },
    VI: { name: "Virgin Islands", url: "default", inactive: "yes", hide:"yes" },
    PR: { name: "Puerto Rico", url: "default", inactive: "yes", hide:"yes" },
    AS: { name: "American Samoa", url: "default", inactive: "yes", hide:"yes" },
    MP: { name: "Northern Mariana Islands", url: "default", inactive: "yes", hide:"yes" }
  },
  locations: {
    "0": {
      name: "New York",
      lat: 40.71,
      lng: -74,
      description: "default",
      color: "default",
      url: "default",
      type: "default",
      size: "default"
    },
    "1": {
      name: "Anchorage",
      lat: 61.2180556,
      lng: -149.9002778,
      color: "default",
      type: "circle"
    }
  },
  labels: {
    NH: {
      parent_id: "NH",
      x: "932",
      y: "183",
      pill: "yes",
      width: 45,
      display: "all"
    },
    VT: {
      parent_id: "VT",
      x: "883",
      y: "243",
      pill: "yes",
      width: 45,
      display: "all"
    },
    RI: {
      parent_id: "RI",
      x: "932",
      y: "273",
      pill: "yes",
      width: 45,
      display: "all"
    },
    NJ: {
      parent_id: "NJ",
      x: "883",
      y: "273",
      pill: "yes",
      width: 45,
      display: "all"
    },
    DE: {
      parent_id: "DE",
      x: "883",
      y: "303",
      pill: "yes",
      width: 45,
      display: "all"
    },
    MD: {
      parent_id: "MD",
      x: "932",
      y: "303",
      pill: "yes",
      width: 45,
      display: "all"
    },
    DC: {
      parent_id: "DC",
      x: "884",
      y: "332",
      pill: "yes",
      width: 45,
      display: "all"
    },
    MA: {
      parent_id: "MA",
      x: "932",
      y: "213",
      pill: "yes",
      width: 45,
      display: "all"
    },
    CT: {
      parent_id: "CT",
      x: "932",
      y: "243",
      pill: "yes",
      width: 45,
      display: "all"
    },
    HI: {
      parent_id: "HI",
      x: 305,
      y: 565,
      pill: "yes"
    },
    AK: {
      parent_id: "AK",
      x: "113",
      y: "495"
    },
    FL: {
      parent_id: "FL",
      x: "773",
      y: "510"
    },
    ME: {
      parent_id: "ME",
      x: "893",
      y: "85"
    },
    NY: {
      parent_id: "NY",
      x: "815",
      y: "158"
    },
    PA: {
      parent_id: "PA",
      x: "786",
      y: "210"
    },
    VA: {
      parent_id: "VA",
      x: "790",
      y: "282"
    },
    WV: {
      parent_id: "WV",
      x: "744",
      y: "270"
    },
    OH: {
      parent_id: "OH",
      x: "700",
      y: "240"
    },
    IN: {
      parent_id: "IN",
      x: "650",
      y: "250"
    },
    IL: {
      parent_id: "IL",
      x: "600",
      y: "250"
    },
    WI: {
      parent_id: "WI",
      x: "575",
      y: "155"
    },
    NC: {
      parent_id: "NC",
      x: "784",
      y: "326"
    },
    TN: {
      parent_id: "TN",
      x: "655",
      y: "340"
    },
    AR: {
      parent_id: "AR",
      x: "548",
      y: "368"
    },
    MO: {
      parent_id: "MO",
      x: "548",
      y: "293"
    },
    GA: {
      parent_id: "GA",
      x: "718",
      y: "405"
    },
    SC: {
      parent_id: "SC",
      x: "760",
      y: "371"
    },
    KY: {
      parent_id: "KY",
      x: "680",
      y: "300"
    },
    AL: {
      parent_id: "AL",
      x: "655",
      y: "405"
    },
    LA: {
      parent_id: "LA",
      x: "550",
      y: "435"
    },
    MS: {
      parent_id: "MS",
      x: "600",
      y: "405"
    },
    IA: {
      parent_id: "IA",
      x: "525",
      y: "210"
    },
    MN: {
      parent_id: "MN",
      x: "506",
      y: "124"
    },
    OK: {
      parent_id: "OK",
      x: "460",
      y: "360"
    },
    TX: {
      parent_id: "TX",
      x: "425",
      y: "435"
    },
    NM: {
      parent_id: "NM",
      x: "305",
      y: "365"
    },
    KS: {
      parent_id: "KS",
      x: "445",
      y: "290"
    },
    NE: {
      parent_id: "NE",
      x: "420",
      y: "225"
    },
    SD: {
      parent_id: "SD",
      x: "413",
      y: "160"
    },
    ND: {
      parent_id: "ND",
      x: "416",
      y: "96"
    },
    WY: {
      parent_id: "WY",
      x: "300",
      y: "180"
    },
    MT: {
      parent_id: "MT",
      x: "280",
      y: "95"
    },
    CO: {
      parent_id: "CO",
      x: "320",
      y: "275"
    },
    UT: {
      parent_id: "UT",
      x: "223",
      y: "260"
    },
    AZ: {
      parent_id: "AZ",
      x: "205",
      y: "360"
    },
    NV: {
      parent_id: "NV",
      x: "140",
      y: "235"
    },
    OR: {
      parent_id: "OR",
      x: "100",
      y: "120"
    },
    WA: {
      parent_id: "WA",
      x: "130",
      y: "55"
    },
    ID: {
      parent_id: "ID",
      x: "200",
      y: "150"
    },
    CA: {
      parent_id: "CA",
      x: "79",
      y: "285"
    },
    MI: {
      parent_id: "MI",
      x: "663",
      y: "185"
    },
    PR: {
      parent_id: "PR",
      x: "620",
      y: "545"
    },
    GU: {
      parent_id: "GU",
      x: "550",
      y: "540"
    },
    VI: {
      parent_id: "VI",
      x: "680",
      y: "519"
    },
    MP: {
      parent_id: "MP",
      x: "570",
      y: "575"
    },
    AS: {
      parent_id: "AS",
      x: "665",
      y: "580"
    }
  }
};
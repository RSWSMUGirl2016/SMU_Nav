package com.example.smu_nav;

import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;
import org.json.JSONException;
import org.json.JSONObject;
import org.w3c.dom.Document;

import android.support.v4.app.FragmentActivity;
import android.support.v4.app.FragmentManager;

import android.app.Activity;
import android.content.Context;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.MapFragment;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.PolylineOptions;

public class MainActivity extends Activity {

	private Button find;
	private EditText buildingName;
	private EditText roomName;
	private EditText roomNumber;

	// Google Map
	private GoogleMap googleMap;

	// GPSTracker class
	private GPSTracker gps;

	private double currLatitude;
	private double currLongitude;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		gps = new GPSTracker(MainActivity.this);
		find = (Button) findViewById(R.id.Find);
		buildingName = (EditText) findViewById(R.id.BuildingName);
		roomName = (EditText) findViewById(R.id.RoomName);
		roomNumber = (EditText) findViewById(R.id.RoomNumber);

		try {
			// Loading map
			initilizeMap();
			googleMap.setMyLocationEnabled(true);
			googleMap.getUiSettings().setZoomControlsEnabled(true);
			googleMap.getUiSettings().setZoomGesturesEnabled(true);
			googleMap.getUiSettings().setCompassEnabled(true);
			googleMap.getUiSettings().setMyLocationButtonEnabled(true);
			googleMap.getUiSettings().setRotateGesturesEnabled(true);
			CameraPosition cameraPosition = new CameraPosition.Builder()
					.target(new LatLng(32.8406452, -96.7831393)).zoom(15)
					.build();

			googleMap.animateCamera(CameraUpdateFactory
					.newCameraPosition(cameraPosition));

		} catch (Exception e) {
			e.printStackTrace();
		}
		if (gps.canGetLocation()) {
			currLatitude = gps.getLatitude();
			currLongitude = gps.getLongitude();
		}

		else {
			// can't get location
			// GPS or Network is not enabled
			// Ask user to enable GPS/network in settings
			gps.showSettingsAlert();
		}

		find.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				String bName = buildingName.getText().toString();
				String rName = roomName.getText().toString();
				String rNumber = roomNumber.getText().toString();
//				JSONObject json = new JSONObject();
//				try {
//					json.put("buildingName", bName);
//					json.put("roomName", rName);
//					json.put("roomNumber", rNumber);
//				} catch (JSONException e) {
//					e.printStackTrace();
//				}
				//getCoordinates(json);
				
				new coordinateRequest(getApplicationContext()).execute(bName, rName,rNumber);
			}
		});
	}

	/**
	 * function to load map. If map is not created it will create it for you
	 * */
	private void initilizeMap() {
		if (googleMap == null) {
			googleMap = ((MapFragment) getFragmentManager().findFragmentById(
					R.id.map)).getMap();

			// check if map is created successfully or not
			if (googleMap == null) {
				Toast.makeText(getApplicationContext(),
						"Sorry! unable to create maps", Toast.LENGTH_SHORT)
						.show();
			}
		}
	}

	@Override
	protected void onResume() {
		super.onResume();
		initilizeMap();
	}
	
	class coordinateRequest extends AsyncTask<String, Void, String>{
    	Context context;
        private coordinateRequest(Context context) {
            this.context = context.getApplicationContext();
        }
    	
		@Override
		protected String doInBackground(String... params) {
			Log.d("buildingName", params[0]);
			Log.d("roomName", params[1]);
			Log.d("roomNumber", params[2]);
			
			
			HttpResponse response = null;
			HttpClient client= new DefaultHttpClient();
			HttpPost post = new HttpPost("http://162.243.227.48/SMU_Nav/api/index.php/getCoordinates");
			
		    
			
			List<NameValuePair> pairs = new ArrayList<NameValuePair>();
			pairs.add(new BasicNameValuePair("buildingName", params[0]));
			pairs.add(new BasicNameValuePair("roomName", params[1]));
			pairs.add(new BasicNameValuePair("roomNumber", params[2]));
			
			try {
				post.setEntity(new UrlEncodedFormEntity(pairs));
			} catch (UnsupportedEncodingException e1) {
				e1.printStackTrace();
			}
			
			try {
				response = client.execute(post);
			} catch (Exception e) {
				
			}
			String responseString = "";
			HttpEntity temp = response.getEntity();
			try {
				responseString = EntityUtils.toString(temp);
			} catch (Exception e) {
				e.printStackTrace();
			} 
			
			
			JSONObject res;
			LatLng fromPosition = null;
			LatLng toPosition = null;
			
			try {
				res = new JSONObject(responseString);
				String x = (String) res.get("x");
				String y = (String) res.get("y");
				fromPosition = new LatLng(currLatitude,currLongitude);
				toPosition = new LatLng(Double.parseDouble(x),Double.parseDouble(y));
			} catch (JSONException e) {
				e.printStackTrace();
			}
			if(toPosition != null){
			GMapV2Direction md = new GMapV2Direction();
			Document doc = md.getDocument(fromPosition, toPosition, GMapV2Direction.MODE_WALKING);
			ArrayList<LatLng> directionPoint = md.getDirection(doc);
			rectLine = new PolylineOptions().width(3).color(Color.RED);

			for(int i = 0 ; i < directionPoint.size() ; i++) {          
			rectLine.add(directionPoint.get(i));
			}
			}

			return responseString;
		}
		
	    PolylineOptions rectLine;

		@Override
		protected void onPostExecute(String response) {
			if(rectLine != null){		
			googleMap.addPolyline(rectLine);
			}
			else{
				String text = "Sorry Location doesn't exist on SMU";
				int duration = Toast.LENGTH_SHORT;
				Toast toast = Toast.makeText(getApplicationContext(), text, duration);
				toast.show();
			}
			
			
	     }  //end of onPostExecute

    	
    } // end of the async
	
	/*
	public void getCoordinates(JSONObject js) {
        
		final String json = js.toString();
		URL url = null;
		try {
			url = new URL(
					"http://162.243.227.48/SMU_Nav/api/index.php/getCoordinates");
		} catch (MalformedURLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		HttpURLConnection connection = null;
		try {
			connection = (HttpURLConnection) url.openConnection();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		connection.setReadTimeout(10000);
		connection.setConnectTimeout(15000);
		try {
			connection.setRequestMethod("POST");
		} catch (ProtocolException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		connection.setRequestProperty("Content-Type", "application/json");
		connection.setRequestProperty("Accept", "application/json");
		
		try {
			connection.getOutputStream().write(json.getBytes());
			connection.getOutputStream().flush();
			connection.connect();
			
			final int statusCode = connection.getResponseCode();
			if (statusCode != HttpURLConnection.HTTP_OK) {
			    Log.d("JMM", "The request failed with status code: " + statusCode + ". Use the status code to debug this problem.");
			} else {
			    InputStream in = new BufferedInputStream(connection.getInputStream());
			    String result = connection.getResponseMessage();
			    Log.d("JMM", result);
			}
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}*/
}

package scraping;

public class Main
{
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff";
	
	public static void main(String[] args)
	{
		Timeline timeline = new Timeline(baseurl);
		
		timeline.scrape();
	}
}
